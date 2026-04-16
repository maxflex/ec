<?php

namespace App\Observers;

use App\Events\CallSummaryUpdatedEvent;
use App\Jobs\CallTranscriptionJob;
use App\Models\Call;
use App\Utils\AI\CallAnalysisService;
use App\Utils\Mango;

class CallObserver
{
    public function created(Call $call): void
    {
        // Дополнительный (4-й) эшелон: как только Call сохранен в БД,
        // принудительно снимаем realtime-active по entry_id и шлем Disconnected.
        Mango::disconnectRealtimeState($call->entry_id);
    }

    public function updated(Call $call): void
    {
        // Добавилась аудиозапись и звонок достаточно длинный для AI-анализа.
        if ($call->wasChanged('has_recording') && $call->has_recording && CallAnalysisService::shouldAnalyze($call)) {
            // запускаем транскрибацию
            CallTranscriptionJob::dispatch($call->id);
        }

        // Для live-кнопки auto-suggest: как только summary реально готово в БД, шлем SSE-событие.
        if ($call->wasChanged('summary')) {
            CallSummaryUpdatedEvent::dispatch($call);
        }
    }
}
