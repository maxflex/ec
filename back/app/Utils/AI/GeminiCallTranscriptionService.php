<?php

namespace App\Utils\AI;

use App\Models\AiPrompt;
use App\Models\Call;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiCallTranscriptionService extends GeminiService
{
    /**
     * Выполняет транскрибацию записи звонка и возвращает текст.
     */
    public static function transcribe(Call $call): string
    {
        if (! $call->has_recording) {
            throw new RuntimeException("Нельзя транскрибировать звонок {$call->id}: нет recording_id");
        }

        [$systemInstructionText, $userPromptText] = app(AiPromptRenderer::class)->renderInstructionAndPromptById(
            AiPrompt::CALL_TRANSCRIPTION, [
                'call' => $call,
                // Передаем АОН в шаблон, чтобы можно было использовать как обычную переменную.
                'aon' => Call::aon($call->number),
            ]);
        $audioBytes = self::downloadRecording($call);

        $result = self::buildModel($systemInstructionText)->generateContent([
            $userPromptText,
            new Blob(
                // В нашем потоке Mango всегда mp3.
                mimeType: MimeType::AUDIO_MP3,
                data: base64_encode($audioBytes),
            ),
        ]);

        return trim($result->text());
    }

    /**
     * Скачивает запись Mango по прямой ссылке и возвращает бинарные данные.
     */
    private static function downloadRecording(Call $call): string
    {
        $recording = $call->getRecording('download');

        $response = Http::retry(2, 1000)
            ->withOptions([
                'proxy' => '37.140.195.195:8888',
                'verify' => false,
                'timeout' => 180,
            ])
            ->get($recording);

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
