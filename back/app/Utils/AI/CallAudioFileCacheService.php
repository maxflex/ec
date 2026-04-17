<?php

namespace App\Utils\AI;

use App\Models\Call;
use Gemini\Data\UploadedFile;
use Gemini\Enums\FileState;
use Gemini\Enums\MimeType;
use Gemini\Responses\Files\MetadataResponse;
use Illuminate\Support\Carbon;
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
     * @return array{uri: string, name: string, mime_type: string, expiration_time: string}|null
     */
    private static function getCachedPayload(string $entryId): ?array
    {
        $cacheKey = self::cacheDataKey($entryId);
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

    private static function cacheDataKey(string $entryId): string
    {
        return "call-recording:{$entryId}";
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

            $metadata = self::geminiClient()
                ->files()
                ->upload(
                    filename: $tempFile,
                    mimeType: MimeType::AUDIO_MP3,
                    displayName: "call-{$call->entry_id}.mp3"
                );

            // В SDK metadataGet() сам префиксует "files/" для короткого имени.
            // upload возвращает name в формате "files/...", поэтому безопаснее опрашивать по полному URI.
            return self::waitUntilFileReady($metadata->uri);
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
        $path = $call->getRecordingStoragePath();
        if (! Storage::exists($path)) {
            throw new RuntimeException("Не найден аудиофайл звонка {$call->id} в Storage по пути {$path}");
        }

        $audio = Storage::get($path);
        if ($audio === '') {
            throw new RuntimeException("Получен пустой аудиофайл для звонка {$call->id}");
        }

        return self::uploadAudioBytesToGemini($call, $audio);
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
    private static function putCachedPayload(string $entryId, array $payload, string $expirationTime): void
    {
        $ttlSeconds = self::resolveRedisTtlSeconds($expirationTime);
        if ($ttlSeconds <= 0) {
            // Если TTL уже истек, бессмысленно писать ключ в Redis.
            return;
        }

        cache()->put(
            self::cacheDataKey($entryId),
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
