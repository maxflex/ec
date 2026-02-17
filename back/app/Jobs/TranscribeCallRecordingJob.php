<?php

namespace App\Jobs;

use App\Models\Call;
use App\Utils\AI\GeminiCallTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranscribeCallRecordingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Небольшой backoff на случай временных ошибок сети/API.
     *
     * @var array<int>
     */
    public array $backoff = [30, 120, 300];

    public function __construct(
        private readonly string $callId,
    ) {}

    public function handle(): void
    {
        $call = Call::findOrFail($this->callId);

        if (! $call->recording) {
            return;
        }

        // Повторно не транскрибируем, если текст уже записан.
        if ($call->transcription) {
            return;
        }

        $transcription = GeminiCallTranscriptionService::transcribe($call);

        if ($transcription === '') {
            return;
        }

        $call->update([
            'transcription' => $transcription,
        ]);
    }
}
