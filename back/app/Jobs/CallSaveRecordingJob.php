<?php

namespace App\Jobs;

use App\Models\Call;
use App\Utils\AI\CallAudioFileCacheService;
use App\Utils\Mango;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CallSaveRecordingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Максимальное время выполнения одной попытки (10 минут).
     */
    public int $timeout = 600;

    /**
     * Ограничиваем число попыток: при корректном роутинге в MR этого достаточно.
     */
    public int $tries = 3;

    /**
     * Небольшая лесенка задержек для кратковременных сбоев очереди/БД.
     *
     * @var array<int>
     */
    public array $backoff = [10, 60];

    public function __construct(
        private readonly string $entryId,
        private readonly string $recordingId,
    ) {}

    public function handle(): void
    {
        /** @var Call|null $call */
        $call = Call::where('entry_id', $this->entryId)->first();

        // Между Summary и RecordAdded бывают гонки: если Call еще не записан,
        // бросаем исключение, чтобы сработал retry по backoff.
        if (! $call) {
            throw new RuntimeException("Звонок с entry_id={$this->entryId} еще не создан");
        }

        $path = $call->getRecordingStoragePath();

        $downloadUrl = Mango::buildRecordingLink($this->recordingId);
        $response = Http::withOptions([
            'proxy' => '37.140.195.195:8888',
            'verify' => false,
            'timeout' => 180,
        ])
            ->retry(3, 1000, throw: false)
            ->get($downloadUrl);

        if (! $response->successful()) {
            throw new RuntimeException(
                "Не удалось скачать запись entry_id={$this->entryId} (HTTP {$response->status()})"
            );
        }

        $audio = $response->body();
        if ($audio === '') {
            throw new RuntimeException("Получен пустой аудиофайл entry_id={$this->entryId}");
        }

        Storage::put($path, $audio);

        // Если в этой попытке реально скачали бинарник, прогреваем Files API заранее.
        // Важно: прогрев не должен ломать ingestion звонка, поэтому ошибки логируем и продолжаем.
        try {
            CallAudioFileCacheService::getOrCreateUploadedFile($call, $audio);
        } catch (Throwable $e) {
            logger()->warning('gemini.files.cache_warmup_failed', [
                'call_id' => $call->id,
                'entry_id' => $this->entryId,
                'error' => $e->getMessage(),
            ]);
        }

        $call->update(['has_recording' => true]);
    }
}
