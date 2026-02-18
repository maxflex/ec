<?php

namespace App\Jobs;

use App\Models\Call;
use App\Utils\AI\GeminiCallService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCallRecordingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Максимальное время выполнения одной попытки (10 минут).
     */
    public int $timeout = 600;

    public int $tries = 2;

    /**
     * Небольшой backoff на случай временных ошибок сети/API.
     *
     * @var array<int>
     */
    public array $backoff = [60];

    public function __construct(
        private readonly string $callId,
    ) {}

    public function handle(): void
    {
        $call = Call::findOrFail($this->callId);

        if (! $call->recording) {
            return;
        }

        $transcriptionAndSummary = GeminiCallService::getTranscriptionAndSummary($call);

        $call->update($transcriptionAndSummary);
    }
}
