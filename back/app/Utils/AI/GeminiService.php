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

    /**
     * Общая сборка instruction для звонков:
     * поддерживаем новое имя ключа transcript и legacy-ключ transcription.
     *
     * @param  'transcript'|'analysis'  $key
     * @return array{
     *     transcript: array{text: string, model: string, created_at: string}|null,
     *     analysis: array{text: string, model: string, created_at: string}|null
     * }
     */
    protected static function mergeCallInstruction(
        mixed $instruction,
        string $key,
        string $snapshot,
        string $model,
    ): array {
        $currentInstruction = is_array($instruction) ? $instruction : [];

        return [
            'transcript' => is_array($currentInstruction['transcript'] ?? null)
                ? $currentInstruction['transcript']
                : (is_array($currentInstruction['transcription'] ?? null) ? $currentInstruction['transcription'] : null),
            'analysis' => is_array($currentInstruction['analysis'] ?? null)
                ? $currentInstruction['analysis']
                : null,
            $key => [
                'text' => $snapshot,
                'model' => $model,
                'created_at' => now()->format('Y-m-d H:i:s'),
            ],
        ];
    }

    protected static function buildInstructionSnapshot(string $systemInstructionText, string $userPromptText): string
    {
        return trim($systemInstructionText)."\n\n<USER_PROMPT>\n\n".trim($userPromptText);
    }
}
