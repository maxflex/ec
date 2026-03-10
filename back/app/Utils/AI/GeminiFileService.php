<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use Gemini\Contracts\Resources\FilesContract;
use Gemini\Data\UploadedFile;
use Gemini\Enums\FileState;
use Gemini\Enums\MimeType;
use Gemini\Responses\Files\MetadataResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GeminiFileService extends GeminiService
{
    // Максимум проверок статуса файла в Gemini после upload.
    private const int FILE_PROCESSING_MAX_ATTEMPTS = 30;

    // Пауза между проверками статуса (0.5s), чтобы не долбить API слишком часто.
    private const int FILE_PROCESSING_SLEEP_SECONDS = 1;

    // За сколько минут до expiry считаем Gemini-файл "протухающим" и перезаливаем.
    private const int FILE_EXPIRATION_BUFFER_MINUTES = 5;

    /**
     * Синхронизирует файлы AI-шаблона с Gemini Files API в момент сохранения шаблона.
     */
    public static function syncPromptFiles(AiPrompt $aiPrompt, mixed $previousPromptFiles = null): AiPrompt
    {
        // Нужен снимок старых URI: если пользователь удалил файл в шаблоне, старый Gemini-объект надо удалить.
        $previousUris = self::collectGeminiUris(self::normalizePromptFiles($previousPromptFiles));
        $syncResult = self::synchronizePromptFiles(
            promptFiles: self::normalizePromptFiles($aiPrompt->files),
            promptId: $aiPrompt->id,
            reuploadExpiring: false
        );

        $currentUris = self::collectGeminiUris($syncResult['files']);
        $obsoleteUris = array_values(array_unique([
            ...$syncResult['obsoleteUris'],
            ...array_diff($previousUris, $currentUris),
        ]));

        // Обновляем JSON только при реальных изменениях (избегаем лишних UPDATE).
        self::updatePromptFilesIfChanged($aiPrompt, $syncResult['files']);
        // Удаляем устаревшие Gemini-файлы, чтобы не копить мусор в проекте.
        self::deleteGeminiFiles($obsoleteUris);

        return $aiPrompt->fresh();
    }

    /**
     * @param  array<int, array<string, mixed>|object>  $promptFiles
     * @return array<int, string>
     */
    private static function collectGeminiUris(array $promptFiles): array
    {
        return collect($promptFiles)
            ->map(fn (array|object $file): ?string => self::extractPromptFileValue($file, 'gemini_file_uri'))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  object|array<string, mixed>  $promptFile
     */
    private static function extractPromptFileValue(object|array $promptFile, string $key): ?string
    {
        $value = is_array($promptFile)
            ? ($promptFile[$key] ?? null)
            : ($promptFile->{$key} ?? null);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    /**
     * @return array<int, array<string, mixed>|object>
     */
    private static function normalizePromptFiles(mixed $promptFiles): array
    {
        return is_array($promptFiles) ? array_values($promptFiles) : [];
    }

    /**
     * Синхронизирует files-поле AI-шаблона с Gemini Files API.
     *
     * @param  array<int, array<string, mixed>|object>  $promptFiles
     * @return array{
     *     files: array<int, array<string, mixed>>,
     *     gemini_files: array<int, UploadedFile>,
     *     obsoleteUris: array<int, string>
     * }
     */
    private static function synchronizePromptFiles(array $promptFiles, int $promptId, bool $reuploadExpiring): array
    {
        if ($promptFiles === []) {
            return [
                'files' => [],
                'gemini_files' => [],
                'obsoleteUris' => [],
            ];
        }

        $initialUris = collect($promptFiles)
            ->map(fn (array|object $file): ?string => self::extractPromptFileValue($file, 'gemini_file_uri'))
            ->filter()
            ->values()
            ->all();

        $normalizedFiles = [];
        $geminiFiles = [];

        foreach ($promptFiles as $index => $promptFile) {
            if (! is_array($promptFile) && ! is_object($promptFile)) {
                continue;
            }

            $normalizedFile = is_array($promptFile) ? $promptFile : (array) $promptFile;
            $fileUrl = self::extractPromptFileValue($normalizedFile, 'url');
            if ($fileUrl === null) {
                continue;
            }

            $geminiFileUri = self::extractPromptFileValue($normalizedFile, 'gemini_file_uri');
            $geminiMimeType = self::extractPromptFileValue($normalizedFile, 'gemini_mime_type');
            $geminiExpiresAt = self::extractPromptFileValue($normalizedFile, 'gemini_expires_at');

            // Если URI отсутствует/протухает/невалиден — поднимаем свежий URI из канонического CDN-файла.
            $isReusable = self::isGeminiFileReusable($geminiFileUri, $geminiMimeType, $geminiExpiresAt);
            if (! $isReusable || ($reuploadExpiring && self::isGeminiFileExpiringSoon($geminiExpiresAt))) {
                $uploadedFile = self::uploadPromptFileByUrl($normalizedFile, $fileUrl, $promptId, $index);
                $geminiFileUri = $uploadedFile->uri;
                $geminiMimeType = $uploadedFile->mimeType;
                $geminiExpiresAt = $uploadedFile->expirationTime;

                // Сохраняем служебные поля Gemini рядом с CDN-данными файла.
                $normalizedFile['gemini_file_uri'] = $geminiFileUri;
                $normalizedFile['gemini_file_name'] = $uploadedFile->name;
                $normalizedFile['gemini_mime_type'] = $geminiMimeType;
                $normalizedFile['gemini_expires_at'] = $geminiExpiresAt;
            }

            if ($geminiFileUri === null || $geminiMimeType === null) {
                throw new RuntimeException("AI-шаблон #{$promptId}: не удалось подготовить вложение #{$index}");
            }

            $mimeTypeEnum = self::mapMimeTypeToEnum($geminiMimeType);
            if ($mimeTypeEnum === null) {
                throw new RuntimeException("AI-шаблон #{$promptId}: MIME '{$geminiMimeType}' не поддерживается Gemini SDK");
            }

            // Это именно формат данных для Gemini generateContent(...), не доменные "files" из нашей БД.
            $geminiFiles[] = new UploadedFile(
                fileUri: $geminiFileUri,
                mimeType: $mimeTypeEnum
            );
            $normalizedFiles[] = $normalizedFile;
        }

        $activeUris = collect($normalizedFiles)
            ->map(fn (array $file): ?string => self::extractPromptFileValue($file, 'gemini_file_uri'))
            ->filter()
            ->values()
            ->all();

        return [
            'files' => $normalizedFiles,
            'gemini_files' => $geminiFiles,
            'obsoleteUris' => array_values(array_diff($initialUris, $activeUris)),
        ];
    }

    /**
     * Проверяет, можно ли использовать уже сохраненный gemini URI без перезаливки.
     */
    private static function isGeminiFileReusable(?string $geminiFileUri, ?string $geminiMimeType, ?string $geminiExpiresAt): bool
    {
        if ($geminiFileUri === null || $geminiMimeType === null || $geminiExpiresAt === null) {
            return false;
        }

        if (self::mapMimeTypeToEnum($geminiMimeType) === null) {
            return false;
        }

        // Даже "живой" URI считаем невалидным, если expiry уже на подходе.
        return ! self::isGeminiFileExpiringSoon($geminiExpiresAt);
    }

    private static function mapMimeTypeToEnum(string $mimeType): ?MimeType
    {
        $normalizedMimeType = strtolower(trim($mimeType));
        $normalizedMimeType = match ($normalizedMimeType) {
            'image/jpg' => 'image/jpeg',
            'audio/mpeg' => 'audio/mp3',
            'audio/x-wav' => 'audio/wav',
            'video/quicktime' => 'video/mov',
            'video/x-msvideo' => 'video/avi',
            'video/x-ms-wmv' => 'video/wmv',
            'text/x-markdown', 'text/md', 'application/markdown' => 'text/markdown',
            'application/xml' => 'text/xml',
            'application/javascript' => 'text/javascript',
            default => $normalizedMimeType,
        };

        return MimeType::tryFrom($normalizedMimeType);
    }

    /**
     * Истекающие файлы перезаливаем заранее, чтобы не упереться в race-condition во время generateContent.
     */
    private static function isGeminiFileExpiringSoon(?string $geminiExpiresAt): bool
    {
        if (! is_string($geminiExpiresAt) || trim($geminiExpiresAt) === '') {
            return true;
        }

        try {
            $expiresAt = Carbon::parse($geminiExpiresAt);
        } catch (Throwable) {
            return true;
        }

        return $expiresAt->lessThanOrEqualTo(now()->addMinutes(self::FILE_EXPIRATION_BUFFER_MINUTES));
    }

    /**
     * Загружает файл из CRM CDN в Gemini Files API и возвращает актуальные метаданные.
     *
     * @param  array<string, mixed>|object  $promptFile
     */
    private static function uploadPromptFileByUrl(
        array|object $promptFile,
        string $fileUrl,
        int $promptId,
        int|string $fileIndex
    ): MetadataResponse {
        $displayName = self::resolvePromptFileDisplayName($promptFile, $fileUrl, $promptId, $fileIndex);
        $downloadedFile = self::downloadPromptFileToTemp($fileUrl, $promptId, $fileIndex);

        try {
            $mimeType = self::resolveGeminiMimeType($downloadedFile['path'], $downloadedFile['mimeTypeHeader']);
            if ($mimeType === null) {
                throw new RuntimeException("AI-шаблон #{$promptId}: у файла '{$displayName}' неподдерживаемый MIME-тип");
            }

            $filesResource = self::geminiClient()->files();
            // Upload возвращает URI сразу, но файл может быть еще в PROCESSING.
            $uploadedFile = $filesResource->upload(
                filename: $downloadedFile['path'],
                mimeType: $mimeType,
                displayName: $displayName
            );

            // Ждем ACTIVE, чтобы downstream generateContent не упал на "не готовом" файле.
            return self::waitForGeminiFileReady($filesResource, $uploadedFile->uri, $promptId, $displayName);
        } finally {
            // Временный файл на диске нужен только на время upload.
            @unlink($downloadedFile['path']);
        }
    }

    /**
     * @param  object|array<string, mixed>  $promptFile
     */
    private static function resolvePromptFileDisplayName(
        object|array $promptFile,
        string $fileUrl,
        int $promptId,
        int|string $fileIndex
    ): string {
        $nameFromPayload = self::extractPromptFileValue($promptFile, 'name');
        if ($nameFromPayload !== null) {
            return $nameFromPayload;
        }

        $path = parse_url($fileUrl, PHP_URL_PATH);
        $fileName = is_string($path) ? basename($path) : '';
        if ($fileName !== '') {
            return $fileName;
        }

        return "ai_prompt_{$promptId}_file_{$fileIndex}";
    }

    /**
     * Скачивает прикрепленный файл во временный файл на диске.
     *
     * @return array{path: string, mimeTypeHeader: ?string}
     */
    private static function downloadPromptFileToTemp(string $fileUrl, int $promptId, int|string $fileIndex): array
    {
        $response = Http::timeout(120)->connectTimeout(15)->get($fileUrl);

        if (! $response->successful()) {
            throw new RuntimeException("AI-шаблон #{$promptId}: не удалось скачать файл #{$fileIndex} (HTTP {$response->status()})");
        }

        $body = $response->body();
        if ($body === '') {
            throw new RuntimeException("AI-шаблон #{$promptId}: получен пустой файл #{$fileIndex}");
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'gemini-prompt-');
        if ($tempPath === false) {
            throw new RuntimeException("AI-шаблон #{$promptId}: не удалось создать временный файл");
        }

        file_put_contents($tempPath, $body);

        $mimeTypeHeader = $response->header('Content-Type');
        if (is_string($mimeTypeHeader) && str_contains($mimeTypeHeader, ';')) {
            $mimeTypeHeader = trim(explode(';', $mimeTypeHeader, 2)[0]);
        }

        return [
            'path' => $tempPath,
            'mimeTypeHeader' => is_string($mimeTypeHeader) && $mimeTypeHeader !== '' ? $mimeTypeHeader : null,
        ];
    }

    private static function resolveGeminiMimeType(string $tempFilePath, ?string $mimeTypeHeader): ?MimeType
    {
        $candidates = [];

        if (is_string($mimeTypeHeader) && $mimeTypeHeader !== '') {
            $candidates[] = $mimeTypeHeader;
        }

        $detectedMimeType = mime_content_type($tempFilePath);
        if (is_string($detectedMimeType) && $detectedMimeType !== '') {
            $candidates[] = $detectedMimeType;
        }

        foreach ($candidates as $candidateMimeType) {
            $mimeType = self::mapMimeTypeToEnum($candidateMimeType);
            if ($mimeType !== null) {
                return $mimeType;
            }
        }

        return null;
    }

    private static function waitForGeminiFileReady(
        FilesContract $filesResource,
        string $uploadedUri,
        int $promptId,
        string $displayName
    ): MetadataResponse {
        $metadata = $filesResource->metadataGet($uploadedUri);
        $attempt = 0;

        while (! $metadata->state->complete()) {
            if ($attempt >= self::FILE_PROCESSING_MAX_ATTEMPTS) {
                throw new RuntimeException("AI-шаблон #{$promptId}: таймаут обработки файла '{$displayName}' в Gemini");
            }

            sleep(self::FILE_PROCESSING_SLEEP_SECONDS);
            $attempt++;
            $metadata = $filesResource->metadataGet($uploadedUri);
        }

        // FAILED лучше явно поднять, чтобы не отправлять битый URI в generateContent.
        if ($metadata->state === FileState::Failed) {
            throw new RuntimeException("AI-шаблон #{$promptId}: Gemini не смог обработать файл '{$displayName}'");
        }

        return $metadata;
    }

    /**
     * Обновляем JSON только если реально изменились метаданные, чтобы не плодить лишние UPDATE.
     *
     * @param  array<int, array<string, mixed>>  $newFiles
     */
    private static function updatePromptFilesIfChanged(AiPrompt $aiPrompt, array $newFiles): void
    {
        $currentFiles = self::normalizePromptFiles($aiPrompt->files);

        if (json_encode($currentFiles) === json_encode($newFiles)) {
            return;
        }

        $aiPrompt->update([
            'files' => $newFiles,
        ]);
    }

    /**
     * @param  array<int, string>  $geminiFileUris
     */
    private static function deleteGeminiFiles(array $geminiFileUris): void
    {
        if ($geminiFileUris === []) {
            return;
        }

        $filesResource = self::geminiClient()->files();
        foreach ($geminiFileUris as $geminiFileUri) {
            try {
                $filesResource->delete($geminiFileUri);
            } catch (Throwable $e) {
                // Удаление "мусора" не должно ронять основной flow.
                Log::warning('Не удалось удалить файл из Gemini Files API', [
                    'uri' => $geminiFileUri,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Возвращает готовые Gemini files для инференса.
     * Если TTL близок к завершению, файлы автоматически перезальются.
     *
     * @return array<int, UploadedFile>
     */
    public static function getPromptGeminiFiles(int $promptId): array
    {
        $aiPrompt = AiPrompt::query()->select(['id', 'files'])->findOrFail($promptId);
        $syncResult = self::synchronizePromptFiles(
            promptFiles: self::normalizePromptFiles($aiPrompt->files),
            promptId: $aiPrompt->id,
            reuploadExpiring: true
        );

        self::updatePromptFilesIfChanged($aiPrompt, $syncResult['files']);
        self::deleteGeminiFiles($syncResult['obsoleteUris']);

        return $syncResult['gemini_files'];
    }
}
