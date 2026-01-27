<?php

namespace App\Utils\AI;

use App\Models\Report;
use OpenAI\Laravel\Facades\OpenAI;

readonly class ChatGPT
{
    public static function improveReport(Report $report)
    {
        $instructions = <<<'TXT'
Ты — аккуратный редактор. Исправляй только орфографию, пунктуацию и явные грамматические ошибки.
Разрешено мягкое замещение просторечий на нейтральные эквиваленты.
Запрещено добавлять новые факты, убирать смысловые части, менять структуру абзацев или сокращать объём.
Сохраняй лексику и стиль автора максимально близко к исходному. Верни только JSON по схеме.
Ты — аккуратный редактор. Исправляй орфографию и грамматику, но строго следуй ограничениям по стилю.

Основные задачи:
1. Исправляй явные ошибки и опечатки.
2. Разрешено мягкое замещение грубых просторечий на нейтральные.
3. Сохраняй лексику, структуру абзацев и объём текста.

СТРОГИЕ ЗАПРЕТЫ (НИКОГДА НЕ ДЕЛАЙ ЭТОГО):
1. ЗАПРЕЩЕНО менять букву "е" на "ё" и наоборот.
2. ЗАПРЕЩЕНО менять дефисы/минусы (-) на тире (—, –) и наоборот.
3. Запрещено добавлять новые факты или убирать смысловые части.

Верни только JSON по схеме.
TXT;

        $input = json_encode([
            'homework_comment' => (string) ($report->homework_comment ?? ''),
            'cognitive_ability_comment' => (string) ($report->cognitive_ability_comment ?? ''),
            'knowledge_level_comment' => (string) ($report->knowledge_level_comment ?? ''),
            'recommendation_comment' => (string) ($report->recommendation_comment ?? ''),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $response = OpenAI::responses()->create([
            'model' => 'gpt-4.1-mini',
            'instructions' => $instructions,
            'input' => $input,
            'temperature' => 0, // чтобы правки были минимальными
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'ReportComments',
                    'schema' => [
                        '$schema' => 'http://json-schema.org/draft-07/schema#',
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'homework_comment' => ['type' => 'string'],
                            'cognitive_ability_comment' => ['type' => 'string'],
                            'knowledge_level_comment' => ['type' => 'string'],
                            'recommendation_comment' => ['type' => 'string'],
                        ],
                        'required' => [
                            'homework_comment',
                            'cognitive_ability_comment',
                            'knowledge_level_comment',
                            'recommendation_comment',
                        ],
                    ],
                    'strict' => true,
                ],
            ],
        ]);

        return json_decode($response->outputText);
    }
}
