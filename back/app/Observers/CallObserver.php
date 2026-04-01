<?php

namespace App\Observers;

use App\Jobs\ProcessCallRecordingJob;
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
        if ($call->wasChanged('recording') && $call->recording && CallAnalysisService::shouldAnalyze($call)) {
            // запускаем транскрибацию
            ProcessCallRecordingJob::dispatch($call->id);
        }
    }
}
