<?php

namespace App\Jobs;

use App\Models\Call;
use App\Utils\AI\CallAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * JOB шага 2: анализ звонка по уже готовому transcript.
 */
class CallAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Выделяем AI-звонки в отдельную очередь, чтобы ограничивать их параллельность отдельно от default.
     */
    public string $queue = 'ai-calls';

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

        if (! $call->has_recording || ! $call->transcript) {
            return;
        }

        // Шаг 2: строим summary/analysis по $call->transcript (контекст из самой модели).
        $call->refresh();
        $analysisData = CallAnalysisService::analyzeTranscript($call);
        $call->update($analysisData);
    }
}
