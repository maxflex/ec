<?php

namespace App\Jobs;

use App\Models\Call;
use App\Utils\AI\CallAnalysisService;
use App\Utils\AI\CallTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * JOB шага 1: транскрибация звонка (ASR).
 * После успешного шага 1 запускаем отдельный JOB шага 2 (анализ).
 */
class CallTranscriptionJob implements ShouldQueue
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
        private readonly int $callId,
    ) {}

    public function handle(): void
    {
        $call = Call::findOrFail($this->callId);

        if (! $call->has_recording || ! CallAnalysisService::shouldAnalyze($call)) {
            return;
        }

        // Шаг 1: получаем/обновляем transcript по текущей инструкции транскрибации.
        $transcriptData = CallTranscriptionService::transcribeAudio($call);
        $call->update($transcriptData);
        $call->refresh();

        // Шаг 2 запускаем отдельной задачей, чтобы retries анализа не трогали ASR.
        CallAnalysisJob::dispatch($call->id);
    }
}
