<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CallType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CallListResource;
use App\Http\Resources\CallResource;
use App\Jobs\CallAnalysisJob;
use App\Jobs\CallTranscriptionJob;
use App\Models\Call;
use App\Utils\AI\CallAnalysisService;
use App\Utils\AI\CallTranscriptionService;
use App\Utils\Phone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CallController extends Controller
{
    protected $filters = [
        'number' => ['number'],
        'gte' => ['date_from'],
        'lte' => ['date_to'],
        'equals' => ['user_id'],
        'multiple' => ['caller_type'],
        'callStatus' => ['call_status'],
        'callDuration' => ['call_duration'],
        'shouldRunAiPipeline' => ['should_run_ai_pipeline'],
    ];

    protected $mapFilters = [
        'date_from' => 'DATE(created_at)',
        'date_to' => 'DATE(created_at)',
    ];

    public function index(Request $request)
    {
        $query = Call::query()
            // Для списка не нужны тяжёлые поля (transcript/summary/analysis/instruction).
            ->select([
                'id',
                'entry_id',
                'user_id',
                'type',
                'number',
                'has_recording',
                'created_at',
                'answered_at',
                'finished_at',
                'caller_type',
            ])
            ->with('user', 'aonPhone.entity')
            ->latest();
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, CallListResource::class);
    }

    public function show(Call $call)
    {
        return new CallResource($call);
    }

    /**
     * Шаг 1: отдельный запуск ASR (audio -> transcript).
     */
    public function transcribe(Call $call): array
    {
        // Получаем "сырой" текст транскрипта из аудио и сохраняем в calls.transcript.
        $transcriptData = CallTranscriptionService::transcribeAudio($call);
        $call->update($transcriptData);

        return $transcriptData;
    }

    /**
     * Шаг 2: отдельный запуск аналитики (transcript -> summary/analysis_1).
     */
    public function analyze(Call $call): array
    {
        // Повторный запуск шага 2 должен вести себя как "первый":
        // очищаем предыдущие результаты анализа перед новым прогоном.
        $call->update([
            'summary' => null,
            'analysis_1' => null,
            'analysis_2' => null,
            'caller_type' => null,
        ]);

        $analysisData = CallAnalysisService::analyzeTranscript($call);
        $call->update($analysisData);

        return $analysisData;
    }

    public function active()
    {
        return Call::getActive();
    }

    /**
     * Массовый запуск AI-пайплайна для отфильтрованных звонков.
     */
    public function runAiPipeline(Request $request): array
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:transcribe_and_analyze,analyze'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $query = Call::query()
            ->select(['id', 'has_recording'])
            ->shouldRunAiPipeline();

        // Для диапазона используем стандартные gte/lte фильтры из базового Controller.
        $this->filterGte($query, $validated['date_from'] ?? null, 'date_from');
        $this->filterLte($query, $validated['date_to'] ?? null, 'date_to');

        $total = (clone $query)->count();
        $queued = 0;
        $mode = $validated['mode'];
        $jobsQuery = clone $query;

        if ($mode === 'transcribe_and_analyze') {
            $jobsQuery->where('has_recording', true);
        } else {
            // Для "анализ" нужен уже готовый transcript.
            $jobsQuery
                ->where('has_recording', true)
                ->whereNotNull('transcript');
        }

        (clone $jobsQuery)->update([
            'summary' => null,
            'analysis_1' => null,
            'analysis_2' => null,
            'caller_type' => null,
        ]);

        (clone $jobsQuery)
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(200, function ($calls) use (&$queued, $mode) {
                foreach ($calls as $call) {
                    if ($mode === 'transcribe_and_analyze') {
                        // В этом режиме анализ запустится автоматически после транскрибации внутри Job.
                        CallTranscriptionJob::dispatch($call->id);
                    } else {
                        // Отдельный запуск шага анализа по уже готовому transcript.
                        CallAnalysisJob::dispatch($call->id);
                    }
                    $queued++;
                }
            });

        return [
            'total' => $total,
            'queued' => $queued,
        ];
    }

    /**
     * Ограничиваем выборку только звонками, где должен работать AI-пайплайн.
     */
    protected function filterShouldRunAiPipeline($query, $value): void
    {
        $query->shouldRunAiPipeline();
    }

    protected function filterNumber(Builder $query, string $number): void
    {
        $number = Phone::clean($number);
        $query->where('number', 'like', "%{$number}%");
    }

    /**
     * UI-статусы:
     * incoming / outgoing / missed / missed_callback.
     */
    protected function filterCallStatus(Builder $query, array $status): void
    {
        $statuses = array_values(array_unique(array_filter(
            $status,
            fn ($value) => is_string($value) && in_array($value, [
                'incoming',
                'outgoing',
                'missed',
                'missed_callback',
            ], true),
        )));

        if ($statuses === []) {
            return;
        }

        // Для мультиселекта объединяем статусы через OR в одной вложенной группе.
        $query->where(function (Builder $statusQuery) use ($statuses) {
            foreach ($statuses as $i => $singleStatus) {
                $whereMethod = $i === 0 ? 'where' : 'orWhere';

                match ($singleStatus) {
                    'incoming' => $statusQuery->{$whereMethod}('type', CallType::incoming->value),
                    'outgoing' => $statusQuery->{$whereMethod}('type', CallType::outgoing->value),
                    'missed' => $statusQuery->{$whereMethod}(function (Builder $missedQuery) {
                        $missedQuery->missedNoCallback();
                    }),
                    'missed_callback' => $statusQuery->{$whereMethod}(function (Builder $missedCallbackQuery) {
                        $missedCallbackQuery->missedWithCallback();
                    }),
                    default => null,
                };
            }
        });
    }

    /**
     * UI-диапазоны:
     * no_conversation / very_short / short / medium / long / very_long.
     */
    protected function filterCallDuration(Builder $query, array $durations): void
    {
        $durations = array_values(array_unique(array_filter(
            $durations,
            fn ($value) => is_string($value) && in_array($value, [
                'no_conversation',
                'very_short',
                'short',
                'medium',
                'long',
                'very_long',
            ], true),
        )));

        if ($durations === []) {
            return;
        }

        // Для мультиселекта объединяем диапазоны через OR в одной вложенной группе.
        $query->where(function (Builder $durationQuery) use ($durations) {
            foreach ($durations as $i => $duration) {
                $whereMethod = $i === 0 ? 'where' : 'orWhere';
                $durationQuery->{$whereMethod}(function (Builder $singleDurationQuery) use ($duration) {
                    match ($duration) {
                        'no_conversation' => $singleDurationQuery->whereNull('answered_at'),
                        'very_short' => $this->applyDurationRangeFilter($singleDurationQuery, null, 10),
                        'short' => $this->applyDurationRangeFilter($singleDurationQuery, 10, 60),
                        'medium' => $this->applyDurationRangeFilter($singleDurationQuery, 60, 300),
                        'long' => $this->applyDurationRangeFilter($singleDurationQuery, 300, 600),
                        'very_long' => $this->applyDurationRangeFilter($singleDurationQuery, 600, null),
                        default => null,
                    };
                });
            }
        });
    }

    /**
     * Фильтрация по длительности разговора в секундах.
     * Берём только звонки, где разговор действительно состоялся.
     */
    private function applyDurationRangeFilter(
        Builder $query,
        ?int $minSeconds,
        ?int $maxSeconds,
    ): void {
        $query
            ->whereNotNull('answered_at')
            ->whereNotNull('finished_at');

        if ($minSeconds !== null) {
            $query->whereRaw('TIMESTAMPDIFF(SECOND, answered_at, finished_at) > ?', [$minSeconds]);
        }

        if ($maxSeconds !== null) {
            $query->whereRaw('TIMESTAMPDIFF(SECOND, answered_at, finished_at) <= ?', [$maxSeconds]);
        }
    }
}
