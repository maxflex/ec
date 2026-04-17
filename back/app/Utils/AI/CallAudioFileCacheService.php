<?php

namespace App\Utils\AI;

use App\Models\Call;
use Gemini\Data\UploadedFile;
use Gemini\Enums\FileState;
use Gemini\Enums\MimeType;
use Gemini\Responses\Files\MetadataResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CallAudioFileCacheService extends GeminiService
{
    /**
     * Возвращает URI-файл из Redis или загружает запись звонка в Gemini Files API и кэширует ссылку.
     * Если $audioBytes передан, используем его для загрузки (без повторного чтения из S3).
     */
    public static function getOrCreateUploadedFile(Call $call, ?string $audioBytes = null): UploadedFile
    {
        $cachedPayload = self::getCachedPayload($call->entry_id);
        if ($cachedPayload !== null) {
            return self::payloadToUploadedFile($cachedPayload);
        }

        $metadata = self::resolveMetadataForUpload($call, $audioBytes);
        $payload = self::buildPayloadFromMetadata($metadata);

        self::putCachedPayload($call->entry_id, $payload, $metadata->expirationTime);

        return new UploadedFile(
            fileUri: $metadata->uri,
            mimeType: MimeType::AUDIO_MP3,
        );
    }

    /**
     * Возвращает 2 mono-файла (left/right), загруженных в Gemini Files API, с отдельным кэшем для каждого канала.
     *
     * @return array{0: UploadedFile, 1: UploadedFile}
     */
    public static function getOrCreateUploadedFilesForStereoTranscription(Call $call, ?string $audioBytes = null): array
    {
        $cachedStereoFiles = self::resolveStereoUploadedFilesFromCache($call);
        if ($cachedStereoFiles !== null) {
            return $cachedStereoFiles;
        }

        $audio = $audioBytes ?? self::readCallRecordingAudioBytes($call);
        if ($audio === '') {
            throw new RuntimeException("Получен пустой аудиоблоб для звонка {$call->id}");
        }

        [$leftPath, $rightPath] = self::splitStereoAudioToMonoPaths($call, $audio);

        try {
            $leftMetadata = self::uploadAudioPathToGemini($call, $leftPath, "call-{$call->entry_id}-left.mp3");
            $rightMetadata = self::uploadAudioPathToGemini($call, $rightPath, "call-{$call->entry_id}-right.mp3");
        } finally {
            @unlink($leftPath);
            @unlink($rightPath);
        }

        $leftPayload = self::buildPayloadFromMetadata($leftMetadata);
        $rightPayload = self::buildPayloadFromMetadata($rightMetadata);

        self::putCachedPayload($call->entry_id, $leftPayload, $leftMetadata->expirationTime, 'left');
        self::putCachedPayload($call->entry_id, $rightPayload, $rightMetadata->expirationTime, 'right');

        return [
            self::payloadToUploadedFile($leftPayload),
            self::payloadToUploadedFile($rightPayload),
        ];
    }

    /**
     * @return array{uri: string, name: string, mime_type: string, expiration_time: string}|null
     */
    private static function getCachedPayload(string $entryId, string $variant = 'source'): ?array
    {
        $cacheKey = self::cacheDataKey($entryId, $variant);
        $payload = cache()->get($cacheKey);
        if (! is_array($payload)) {
            return null;
        }

        if (
            ! isset($payload['uri'], $payload['name'], $payload['mime_type'], $payload['expiration_time'])
            || ! is_string($payload['uri'])
            || ! is_string($payload['name'])
            || ! is_string($payload['mime_type'])
            || ! is_string($payload['expiration_time'])
        ) {
            // Битый payload лучше удалить сразу, чтобы не повторять бесполезные проверки на каждом вызове.
            cache()->forget($cacheKey);

            return null;
        }

        return $payload;
    }

    private static function cacheDataKey(string $entryId, string $variant = 'source'): string
    {
        if ($variant === 'source') {
            return "call-recording:{$entryId}";
        }

        // Для left/right держим отдельные ключи, чтобы не смешивать каналы.
        return "call-recording:{$entryId}:{$variant}";
    }

    /**
     * @return array{0: UploadedFile, 1: UploadedFile}|null
     */
    private static function resolveStereoUploadedFilesFromCache(Call $call): ?array
    {
        $leftPayload = self::getCachedPayload($call->entry_id, 'left');
        $rightPayload = self::getCachedPayload($call->entry_id, 'right');

        if ($leftPayload === null || $rightPayload === null) {
            return null;
        }

        return [
            self::payloadToUploadedFile($leftPayload),
            self::payloadToUploadedFile($rightPayload),
        ];
    }

    private static function uploadAudioPathToGemini(Call $call, string $path, string $displayName): MetadataResponse
    {
        $metadata = self::geminiClient()
            ->files()
            ->upload(
                filename: $path,
                mimeType: MimeType::AUDIO_MP3,
                displayName: $displayName
            );

        // В SDK metadataGet() сам префиксует "files/" для короткого имени.
        // upload возвращает name в формате "files/...", поэтому безопаснее опрашивать по полному URI.
        return self::waitUntilFileReady($metadata->uri);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function splitStereoAudioToMonoPaths(Call $call, string $audio): array
    {
        $inputPath = tempnam(sys_get_temp_dir(), 'call-audio-src-');
        $leftPath = tempnam(sys_get_temp_dir(), 'call-audio-left-');
        $rightPath = tempnam(sys_get_temp_dir(), 'call-audio-right-');

        if (! is_string($inputPath) || ! is_string($leftPath) || ! is_string($rightPath)) {
            throw new RuntimeException("Не удалось создать временные файлы для стерео-разделения звонка {$call->id}");
        }

        try {
            if (file_put_contents($inputPath, $audio) === false) {
                throw new RuntimeException("Не удалось записать исходное аудио во временный файл для звонка {$call->id}");
            }

            // Разводим left/right в 2 отдельных mono mp3-файла и затем прикладываем оба в один запрос Gemini.
            $result = Process::timeout(60)->run([
                'ffmpeg',
                '-y',
                '-i', $inputPath,
                '-filter_complex', 'channelsplit=channel_layout=stereo[left][right]',
                '-map', '[left]',
                '-ac', '1',
                '-f', 'mp3',
                $leftPath,
                '-map', '[right]',
                '-ac', '1',
                '-f', 'mp3',
                $rightPath,
            ]);

            if (! $result->successful()) {
                throw new RuntimeException(
                    "Не удалось разделить стерео-аудио звонка {$call->id} через ffmpeg: ".$result->errorOutput()
                );
            }
        } finally {
            @unlink($inputPath);
        }

        return [$leftPath, $rightPath];
    }

    /**
     * @param  array{uri: string, name: string, mime_type: string, expiration_time: string}  $payload
     */
    private static function payloadToUploadedFile(array $payload): UploadedFile
    {
        $mimeType = MimeType::tryFrom($payload['mime_type']) ?? MimeType::AUDIO_MP3;

        return new UploadedFile(
            fileUri: $payload['uri'],
            mimeType: $mimeType,
        );
    }

    private static function uploadAudioBytesToGemini(Call $call, string $audio): MetadataResponse
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'call-audio-');
        if (! is_string($tempFile) || $tempFile === '') {
            throw new RuntimeException("Не удалось создать временный файл для звонка {$call->id}");
        }

        try {
            if (file_put_contents($tempFile, $audio) === false) {
                throw new RuntimeException("Не удалось записать временный аудиофайл для звонка {$call->id}");
            }

            return self::uploadAudioPathToGemini($call, $tempFile, "call-{$call->entry_id}.mp3");
        } finally {
            @unlink($tempFile);
        }
    }

    private static function resolveMetadataForUpload(Call $call, ?string $audioBytes): MetadataResponse
    {
        // null => читаем из Storage, строка => используем уже скачанный бинарник из текущего пайплайна.
        if ($audioBytes === null) {
            return self::uploadCallRecordingToGemini($call);
        }

        if ($audioBytes === '') {
            throw new RuntimeException("Получен пустой аудиоблоб для звонка {$call->id}");
        }

        return self::uploadAudioBytesToGemini($call, $audioBytes);
    }

    /**
     * После upload файл может быть в PROCESSING: дожидаемся ACTIVE ограниченное число попыток.
     */
    private static function waitUntilFileReady(string $fileNameOrUri): MetadataResponse
    {
        $attempts = 3;
        $sleepSeconds = 2;

        for ($i = 1; $i <= $attempts; $i++) {
            $metadata = self::geminiClient()->files()->metadataGet($fileNameOrUri);

            if ($metadata->state === FileState::Active) {
                return $metadata;
            }

            if ($metadata->state === FileState::Failed) {
                throw new RuntimeException("Gemini file {$metadata->name} завершился со статусом FAILED");
            }

            if ($i < $attempts) {
                sleep($sleepSeconds);
            }
        }

        throw new RuntimeException('Gemini file не стал ACTIVE за отведенное время');
    }

    private static function uploadCallRecordingToGemini(Call $call): MetadataResponse
    {
        return self::uploadAudioBytesToGemini($call, self::readCallRecordingAudioBytes($call));
    }

    private static function readCallRecordingAudioBytes(Call $call): string
    {
        $path = $call->getRecordingStoragePath();
        if (! Storage::exists($path)) {
            throw new RuntimeException("Не найден аудиофайл звонка {$call->id} в Storage по пути {$path}");
        }

        $audio = Storage::get($path);
        if ($audio === '') {
            throw new RuntimeException("Получен пустой аудиофайл для звонка {$call->id}");
        }

        return $audio;
    }

    /**
     * @return array{uri: string, name: string, mime_type: string, expiration_time: string}
     */
    private static function buildPayloadFromMetadata(MetadataResponse $metadata): array
    {
        return [
            'uri' => $metadata->uri,
            'name' => $metadata->name,
            // Для .mp3 принудительно сохраняем ожидаемый MIME, чтобы стабильно собрать UploadedFile.
            'mime_type' => MimeType::AUDIO_MP3->value,
            'expiration_time' => $metadata->expirationTime,
        ];
    }

    /**
     * @param  array{uri: string, name: string, mime_type: string, expiration_time: string}  $payload
     */
    private static function putCachedPayload(
        string $entryId,
        array $payload,
        string $expirationTime,
        string $variant = 'source'
    ): void {
        $ttlSeconds = self::resolveRedisTtlSeconds($expirationTime);
        if ($ttlSeconds <= 0) {
            // Если TTL уже истек, бессмысленно писать ключ в Redis.
            return;
        }

        cache()->put(
            self::cacheDataKey($entryId, $variant),
            $payload,
            now()->addSeconds($ttlSeconds),
        );
    }

    private static function resolveRedisTtlSeconds(string $expirationTime): int
    {
        try {
            $expiresAt = Carbon::parse($expirationTime);
        } catch (Throwable) {
            // Консервативный fallback, если формат даты неожиданно изменится.
            return 3600;
        }

        return now()->diffInSeconds($expiresAt, false);
    }
}
