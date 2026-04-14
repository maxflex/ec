<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CallType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CallListResource;
use App\Http\Resources\CallResource;
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
        'equals' => ['user_id'],
        'multiple' => ['caller_type'],
        'callStatus' => ['call_status'],
        'callDuration' => ['call_duration'],
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
                'recording',
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

    public function recording($action, Call $call)
    {
        return $call->getRecording($action);
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
     * no_conversation / short / medium / long / very_long.
     */
    protected function filterCallDuration(Builder $query, array $durations): void
    {
        $durations = array_values(array_unique(array_filter(
            $durations,
            fn ($value) => is_string($value) && in_array($value, [
                'no_conversation',
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
                        'short' => $this->applyDurationRangeFilter($singleDurationQuery, null, 59),
                        'medium' => $this->applyDurationRangeFilter($singleDurationQuery, 60, 300),
                        'long' => $this->applyDurationRangeFilter($singleDurationQuery, 301, 600),
                        'very_long' => $this->applyDurationRangeFilter($singleDurationQuery, 601, null),
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
            ->whereNotNull('finished_at')
            // Защита от кривых данных: отрицательная длительность нам не нужна.
            ->whereRaw('TIMESTAMPDIFF(SECOND, answered_at, finished_at) >= 0');

        if ($minSeconds !== null) {
            $query->whereRaw('TIMESTAMPDIFF(SECOND, answered_at, finished_at) >= ?', [$minSeconds]);
        }

        if ($maxSeconds !== null) {
            $query->whereRaw('TIMESTAMPDIFF(SECOND, answered_at, finished_at) <= ?', [$maxSeconds]);
        }
    }

}
