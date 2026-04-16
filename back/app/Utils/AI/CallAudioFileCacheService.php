<?php

namespace App\Utils\AI;

use App\Models\Call;
use Gemini\Data\UploadedFile;
use Gemini\Enums\FileState;
use Gemini\Enums\MimeType;
use Gemini\Responses\Files\MetadataResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CallAudioFileCacheService extends GeminiService
{
    private const string REDIS_KEY_PREFIX = 'ai:call-audio-file';
    private const int PROCESSING_POLL_ATTEMPTS = 3;
    private const int PROCESSING_POLL_SLEEP_SECONDS = 2;

    /**
     * Возвращает URI-файл из Redis или загружает запись звонка в Gemini Files API и кэширует ссылку.
     */
    public static function getOrCreateUploadedFile(Call $call): UploadedFile
    {
        if (! $call->has_recording) {
            throw new RuntimeException("Нельзя получить AI-аудио для звонка {$call->id}: has_recording=false");
        }

        $cachedPayload = self::getCachedPayload($call->entry_id);
        if ($cachedPayload !== null) {
            logger()->info('gemini.files.cache_hit', [
                'call_id' => $call->id,
                'entry_id' => $call->entry_id,
                'file_name' => $cachedPayload['name'],
            ]);

            return self::payloadToUploadedFile($cachedPayload);
        }

        $metadata = self::uploadCallRecordingToGemini($call);
        $payload = [
            'uri' => $metadata->uri,
            'name' => $metadata->name,
            // Для .mp3 принудительно сохраняем ожидаемый MIME, чтобы стабильно собрать UploadedFile.
            'mime_type' => MimeType::AUDIO_MP3->value,
            'expiration_time' => $metadata->expirationTime,
        ];

        self::putCachedPayload($call->entry_id, $payload, $metadata->expirationTime);

        logger()->info('gemini.files.cache_miss_uploaded', [
            'call_id' => $call->id,
            'entry_id' => $call->entry_id,
            'file_name' => $metadata->name,
            'expires_at' => $metadata->expirationTime,
        ]);

        return self::payloadToUploadedFile($payload);
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

            return self::waitUntilFileReady($metadata->name);
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * После upload файл может быть в PROCESSING: дожидаемся ACTIVE ограниченное число попыток.
     */
    private static function waitUntilFileReady(string $fileName): MetadataResponse
    {
        $attempts = self::PROCESSING_POLL_ATTEMPTS;
        $sleepSeconds = self::PROCESSING_POLL_SLEEP_SECONDS;

        for ($i = 1; $i <= $attempts; $i++) {
            $metadata = self::geminiClient()->files()->metadataGet($fileName);

            if ($metadata->state === FileState::Active) {
                return $metadata;
            }

            if ($metadata->state === FileState::Failed) {
                throw new RuntimeException("Gemini file {$fileName} завершился со статусом FAILED");
            }

            if ($i < $attempts) {
                sleep($sleepSeconds);
            }
        }

        throw new RuntimeException("Gemini file {$fileName} не стал ACTIVE за отведенное время");
    }

    /**
     * @return array{uri: string, name: string, mime_type: string, expiration_time: string}|null
     */
    private static function getCachedPayload(string $entryId): ?array
    {
        $payload = Cache::store('redis')->get(self::cacheDataKey($entryId));
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
            return null;
        }

        return $payload;
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

        Cache::store('redis')->put(
            self::cacheDataKey($entryId),
            $payload,
            now()->addSeconds($ttlSeconds),
        );
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

    private static function cacheDataKey(string $entryId): string
    {
        return self::REDIS_KEY_PREFIX.":{$entryId}";
    }
}
