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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

// use Gemini\Data\GenerationConfig;
// use Gemini\Enums\ResponseMimeType;

class GeminiReportService
{
    private const USER_PROMPT_SEPARATOR = '<USER_PROMPT>';
    private const INSTRUCTION_MACRO_ID = 24;
    private const REPORT_MACRO_ID = 25;

    /**
     * Генерирует улучшенный отчет на основе черновика преподавателя.
     * Если передан $company — тестовый режим (инструкции из макроса).
     * Если $company не передан — реальный режим (инструкции из blade).
     *
     * @return array{comment: string} Улучшенный текст отчета
     */
    public static function improveReport(Report $report, ?Company $company = null): array
    {
        $data = [
            'report' => $report,
            'historyReport' => self::getHistoryReport($report),
            'examples' => self::reportExamples(),
            'perfectLength' => intval(Report::PERFECT_LENGTH * 0.8),
        ];

        $instructionText = $company
            ? self::renderTestInstructionText($company, $data)
            : view('ai.report-instructions', $data)->render();

        return self::generate($instructionText);
    }

    /**
     * В тестовом режиме:
     * 1) берем "каркас" инструкции из макроса 24;
     * 2) подменяем include('ai.report') на рендер макроса 25;
     * 3) выбираем text_{company} для обоих макросов (ООО/ИП/АНО).
     *
     * @param  array<string, mixed>  $data
     */
    private static function renderTestInstructionText(Company $company, array $data): string
    {
        $field = 'text_'.$company->value;
        $instructionTemplate = Macro::find(self::INSTRUCTION_MACRO_ID)?->{$field} ?? '';
        $reportTemplate = Macro::find(self::REPORT_MACRO_ID)?->{$field} ?? '';

        // Если отдельный макрос отчета пустой, сохраняем прежнее поведение.
        if (! $reportTemplate) {
            return Blade::render($instructionTemplate, $data);
        }

        $instructionTemplate = self::replaceAiReportIncludes($instructionTemplate);
        $data['renderReport'] = static function (Report $report, string $letter) use ($reportTemplate): string {
            // Рендерим "тело отчета" из макроса 25, чтобы его можно было менять из CRM.
            return Blade::render($reportTemplate, [
                'report' => $report,
                'letter' => $letter,
            ]);
        };

        return Blade::render($instructionTemplate, $data);
    }

    private static function replaceAiReportIncludes(string $template): string
    {
        return preg_replace_callback(
            "/@include\\(\\s*['\"]ai\\.report['\"]\\s*,\\s*\\[\\s*'report'\\s*=>\\s*\\$(?<reportVar>[a-zA-Z_][a-zA-Z0-9_]*)\\s*,\\s*'letter'\\s*=>\\s*'(?<letter>[^']+)'\\s*]\\s*\\)/",
            static fn (array $matches): string => "{!! \$renderReport(\${$matches['reportVar']}, '{$matches['letter']}') !!}",
            $template
        ) ?? $template;
    }

    private static function getHistoryReport(Report $report): ?Report
    {
        // История отчетов в плоскости (по этому ученику, по этой программе, в этом году, у этого препода)
        return Report::where([
            ['id', '<', $report->id],
            ['client_id', $report->client_id],
            ['teacher_id', $report->teacher_id],
            ['program', $report->program],
            ['year', $report->year],
        ])->latest()->first();
    }

    /**
     * @return Collection<int, Report>
     */
    private static function reportExamples(): Collection
    {
        return Report::whereIn('id', [
            52104, // идеальный
            51533, // идеальный
            50488, // средний
            47675, // проблемный
        ])->get();
    }

    /**
     * @return array{comment: string}
     */
    private static function generate(string $instructionText): array
    {
        $client = Gemini::client(config('gemini.api_key'));

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
