<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Call;
use Gemini\Data\Blob;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\ResponseMimeType;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiCallRecordingService extends GeminiService
{
    /**
     * Возвращает транскрибацию и краткое содержание записи звонка.
     *
     * @return array{summary: string, transcription: string}
     */
    public static function getTranscriptionAndSummary(Call $call): array
    {
        if (! $call->has_recording) {
            throw new RuntimeException("Нельзя транскрибировать звонок {$call->id}: нет recording_id");
        }

        // Рендерим промпты (инструкция по транскрибации + контекст звонка)
        [$systemInstructionText, $userPromptText] = app(AiPromptRenderer::class)->renderInstructionAndPromptById(
            AiPrompt::CALL_TRANSCRIPTION, [
                'call' => $call,
                'aon' => Call::aon($call->number),
            ]);

        // Скачиваем аудио
        $audioBytes = self::downloadRecording($call);

        // Описываем схему JSON для строгого ответа
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'summary' => new Schema(
                    type: DataType::STRING,
                    description: 'Краткое содержание звонка: кто звонил, цель, итог.'
                ),
                'transcription' => new Schema(
                    type: DataType::STRING,
                    description: 'Полная транскрибация диалога с разделением по спикерам.'
                ),
            ],
            required: ['summary', 'transcription']
        );

        // Формируем запрос к модели
        $response = self::buildModel($systemInstructionText)
            ->withGenerationConfig(
                new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: $schema
                )
            )
            ->generateContent([
                $userPromptText,
                new Blob(
                    mimeType: MimeType::AUDIO_MP3,
                    data: base64_encode($audioBytes),
                ),
            ]);

        return $response->json(true);
    }

    /**
     * Скачивает запись Mango по прямой ссылке и возвращает бинарные данные.
     */
    private static function downloadRecording(Call $call): string
    {
        $recording = $call->getRecording('download');

        $response = Http::withOptions([
            'proxy' => '37.140.195.195:8888',
            'verify' => false,
            'timeout' => 180,
        ])->get($recording);

        if (! $response->successful()) {
            throw new RuntimeException("Не удалось скачать запись звонка {$call->id} (HTTP {$response->status()})");
        }

        $body = $response->body();
        if ($body === '') {
            throw new RuntimeException("Получен пустой аудиофайл для звонка {$call->id}");
        }

        return $body;
    }
}
