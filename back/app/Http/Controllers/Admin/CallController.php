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
     * Шаг 2: отдельный запуск аналитики (transcript -> summary/analysis_1..3).
     */
    public function analyze(Call $call): array
    {
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
     * incoming / outgoing / missed / missed_callback / missed_all.
     */
    protected function filterCallStatus(Builder $query, string $status): void
    {
        match ($status) {
            'incoming' => $query->where('type', CallType::incoming->value),
            'outgoing' => $query->where('type', CallType::outgoing->value),
            'missed' => $query->missedNoCallback(),
            'missed_callback' => $query->missedWithCallback(),
            // Все пропущенные входящие независимо от того, перезванивали по ним или нет.
            'missed_all' => $query
                ->where('type', CallType::incoming->value)
                ->whereNull('answered_at'),
            default => null,
        };
    }
}
