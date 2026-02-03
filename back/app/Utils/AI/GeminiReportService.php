<?php

namespace App\Utils\AI;

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
    /**
     * Генерирует улучшенный отчет на основе черновика преподавателя.
     *
     * @return array{comment: string} Улучшенный текст отчета
     */
    public static function improveReport(Report $report): array
    {
        // История отчетов в плоскости (по этому ученику, по этой программе, в этом году, у этого препода)
        $history = Report::where([
            ['id', '<', $report->id],
            ['client_id', $report->client_id],
            ['teacher_id', $report->teacher_id],
            ['program', $report->program],
            ['year', $report->year],
        ])->get();

        $examples = Report::whereIn('id', [52118, 52109, 52100, 52061, 51607])->get();

        $client = Gemini::client(config('gemini.api_key'));

        // Рендеринг шаблона в строку
        // $instructionText = view('ai.report-instructions', [
        //     'report' => $report,
        //     'history' => $history,
        //     'examples' => $examples,
        //     'perfectLength' => intval(Report::PERFECT_LENGTH * 0.8),
        // ])->render();

        $macro = Macro::find(24);
        $instructionText = Blade::render($macro->text_ooo, [
            'report' => $report,
            'history' => $history,
            'examples' => $examples,
            'perfectLength' => intval(Report::PERFECT_LENGTH * 0.8),
        ]);

        // if (is_localhost()) {
        Storage::disk('local')->put('public/ai.txt', $instructionText);
        // }

        // Описываем жесткую структуру ответа (Схему)
        // Мы требуем вернуть объект с единственным полем "comment" типа STRING.
        $schema = new Schema(
            type: DataType::OBJECT,
            properties: [
                'comment' => new Schema(
                    type: DataType::STRING,
                    description: 'Текст отчета. Простой текст без Markdown и HTML тегов.'
                ),
            ],
            required: ['comment']
        );

        $result = $client
            ->generativeModel('gemini-3-flash-preview')
            ->withSystemInstruction(Content::parse($instructionText))
            ->withGenerationConfig(
                generationConfig: new GenerationConfig(
                    responseMimeType: ResponseMimeType::APPLICATION_JSON,
                    responseSchema: $schema,
                )
            )
            ->generateContent($report->comment);

        return $result->json(true);
    }
}
