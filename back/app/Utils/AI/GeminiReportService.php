<?php

namespace App\Utils\AI;

use App\Enums\Company;
use App\Models\Macro;
use App\Models\Report;
use Gemini;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Enums\ResponseMimeType;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

// use Gemini\Data\GenerationConfig;
// use Gemini\Enums\ResponseMimeType;

class GeminiReportService
{
    private const USER_PROMPT_SEPARATOR = '<USER_PROMPT>';

    /**
     * Генерирует улучшенный отчет на основе черновика преподавателя.
     *
     * @param  Company  $company  компания (для теста через макросы)
     * @return array{comment: string} Улучшенный текст отчета
     */
    public static function improveReport(Report $report, Company $company): array
    {
        // История отчетов в плоскости (по этому ученику, по этой программе, в этом году, у этого препода)
        $history = Report::where([
            ['id', '<', $report->id],
            ['client_id', $report->client_id],
            ['teacher_id', $report->teacher_id],
            ['program', $report->program],
            ['year', $report->year],
        ])->get();

        $examples = Report::whereIn('id', [
            52104, // идеальный
            51533, // идеальный
            50488, // средний
            47675, // проблемный
        ])->get();

        $client = Gemini::client(config('gemini.api_key'));

        // Рендеринг шаблона в строку
        // $instructionText = view('ai.report-instructions', [
        //     'report' => $report,
        //     'history' => $history,
        //     'examples' => $examples,
        //     'perfectLength' => intval(Report::PERFECT_LENGTH * 0.8),
        // ])->render();

        $macro = Macro::find(24);
        $instructionText = Blade::render($macro->{'text_'.$company->value}, [
            'report' => $report,
            'history' => $history,
            'examples' => $examples,
            'perfectLength' => intval(Report::PERFECT_LENGTH * 0.8),
        ]);

        [$systemInstructionText, $userPromptText] = self::splitInstructionAndPrompt($instructionText);

        Storage::disk('local')->put('public/ai.txt', trim(collect([
            $systemInstructionText,
            PHP_EOL, PHP_EOL,
            self::USER_PROMPT_SEPARATOR,
            PHP_EOL, PHP_EOL,
            $userPromptText,
        ])->join('')));

        // return [];

        // Описываем жесткую структуру ответа (Схему)
        // Мы требуем вернуть объект с единственным полем "comment" типа STRING.
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'comment' => new Schema(
                    type: DataType::STRING,
                    description: 'Текст отчета с HTML тегами'
                ),
            ],
            required: ['comment']
        );

        $result = $client
            ->generativeModel('gemini-3-flash-preview')
            ->withSystemInstruction(Content::parse($systemInstructionText))
            ->withGenerationConfig(
                generationConfig: new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: $schema,
                )
            )
            ->generateContent($userPromptText);

        return $result->json(true);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function splitInstructionAndPrompt(string $text): array
    {
        $parts = explode(self::USER_PROMPT_SEPARATOR, $text, 2);
        if (count($parts) !== 2) {
            return [trim($text), ''];
        }

        $systemInstructionText = trim($parts[0]);
        $userPromptText = trim($parts[1]);

        return [$systemInstructionText, $userPromptText];
    }
}
