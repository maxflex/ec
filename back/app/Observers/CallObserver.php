<?php

namespace App\Observers;

use App\Jobs\ProcessCallRecordingJob;
use App\Models\Call;
use App\Utils\AI\CallAnalysisService;

class CallObserver
{
    public function updated(Call $call): void
    {
        // Добавилась аудиозапись и звонок достаточно длинный для AI-анализа.
        if ($call->wasChanged('recording') && $call->recording && CallAnalysisService::shouldAnalyze($call)) {
            // запускаем транскрибацию
            ProcessCallRecordingJob::dispatch($call->id);
        }
    }
}
