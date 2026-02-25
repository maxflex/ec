<?php

namespace App\Utils\AI;

use Gemini;
use Gemini\Client;
use Gemini\Data\Content;
use Gemini\Resources\GenerativeModel;

abstract class GeminiService
{
    /**
     * Единая сборка generative model с опциональной system instruction.
     */
    protected static function buildModel(
        ?string $systemInstructionText = null,
        string $model = 'gemini-3-flash-preview'
    ): GenerativeModel {
        $model = self::geminiClient()->generativeModel($model);

        // Инструкцию отправляем только если она реально задана.
        if (is_string($systemInstructionText) && trim($systemInstructionText) !== '') {
            $model = $model->withSystemInstruction(Content::parse($systemInstructionText));
        }

        return $model;
    }

    /**
     * Единая точка создания Gemini-клиента для всех AI-сервисов.
     */
    protected static function geminiClient(): Client
    {
        return Gemini::client(config('gemini.api_key'));
    }
}
