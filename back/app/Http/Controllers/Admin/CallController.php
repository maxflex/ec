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
        'equals' => ['user_id', 'caller_type'],
        'callStatus' => ['call_status'],
    ];

    public function index(Request $request)
    {
        $query = Call::query()
            ->with('user')
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
     * Шаг 2: отдельный запуск аналитики (transcript -> summary/analysis).
     */
    public function analyze(Call $call): array
    {
        // Повторный запуск шага 2 должен вести себя как "первый":
        // очищаем предыдущие результаты анализа перед новым прогоном.
        $call->update([
            'summary' => null,
            'analysis' => null,
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
}
