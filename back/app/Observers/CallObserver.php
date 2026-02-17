<?php

namespace App\Observers;

use App\Jobs\TranscribeCallRecordingJob;
use App\Models\Call;

class CallObserver
{
    public function updated(Call $call): void
    {
        // добавилась аудиозапись разговора + разговор более 10 сек
        if ($call->wasChanged('recording') && $call->recording && $call->duration && $call->duration > 10) {
            // запускаем транскрибацию
            TranscribeCallRecordingJob::dispatch($call->id);
        }
    }
}
