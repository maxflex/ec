<?php

namespace App\Utils;

use App\Models\Report;
use OpenAI\Laravel\Facades\OpenAI;

readonly class ChatGPT
{
    public static function improveReport(Report $report)
    {
        $instructions = <<<'TXT'
Ты — аккуратный редактор. Исправляй орфографию и грамматику, но строго следуй требованиям.

ОСНОВНЫЕ ЗАДАЧИ:
1. Исправляй только явные ошибки и опечатки.
2. Разрешено мягкое замещение грубых просторечий на нейтральные формулировки.
3. Сохраняй исходную лексику, структуру абзацев и общий объём текста.
4. Не меняй стиль автора.

АБСОЛЮТНЫЕ СТИЛЕВЫЕ ЗАПРЕТЫ:
1. Запрещено добавлять новые факты или убирать смысловые части.
2. Запрещено переписывать текст “по-своему”.
3. Запрещено расширять или сокращать объём текста.

ОСОБОЕ ПРАВИЛО НЕИЗМЕНЯЕМЫХ СИМВОЛОВ:
Ты обязан сохранять ВСЕ символы «е», «ё», «-», «–», «—» ровно такими, какими они были во входном тексте.
Эти символы являются полностью неизменяемыми.

Это значит:
- Если во входном тексте стоит «е» — оставляй «е».
- Если стоит «ё» — оставляй «ё».
- Если стоит «-» — оставляй «-».
- Если стоит «–» — оставляй «–».
- Если стоит «—» — оставляй «—».

Ты не имеешь права:
- заменять «е» на «ё» или «ё» на «е»,
- заменять «-» на «–» или «—»,
- заменять «–» на «-» или «—»,
- заменять «—» на «-» или «–»,

Изменение хотя бы одного из этих символов делает результат недопустимым.

ФОРМАТ ВЫВОДА:
Верни только JSON строго по заданной схеме.
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
