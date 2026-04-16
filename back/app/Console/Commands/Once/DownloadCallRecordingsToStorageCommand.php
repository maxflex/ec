<?php

namespace App\Console\Commands\Once;

use App\Models\Call;
use App\Utils\Mango;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DownloadCallRecordingsToStorageCommand extends Command
{
    protected $signature = 'once:download-call-recordings-to-storage {--force : Перезаписать уже загруженные файлы}';

    protected $description = 'Скачивает все записи звонков из Mango и сохраняет в Storage по пути calls/{entry_id}.mp3';

    public function handle(): int
    {
        if (! Schema::hasColumn('calls', 'recording')) {
            $this->error('Колонка calls.recording уже удалена. Эта one-time команда больше неактуальна.');

            return self::FAILURE;
        }

        $baseQuery = Call::query()
            ->select(['id', 'entry_id', 'recording'])
            ->whereNotNull('recording')
            ->whereNotNull('entry_id');

        $total = (clone $baseQuery)->count();
        if ($total === 0) {
            $this->info('Нет звонков с recording_id для выгрузки.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $downloaded = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        $this->line("Найдено звонков для обработки: {$total}");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Идем чанками, чтобы не держать все звонки в памяти.
        (clone $baseQuery)
            ->orderBy('id')
            ->chunkById(100, function ($calls) use ($force, &$downloaded, &$skipped, &$failed, &$errors, $bar) {
                foreach ($calls as $call) {
                    $path = $this->buildStoragePath((string) $call->entry_id);

                    if (! $force && Storage::exists($path)) {
                        $skipped++;
                        $bar->advance();

                        continue;
                    }

                    try {
                        $audio = $this->downloadRecording($call);
                        Storage::put($path, $audio);
                        $downloaded++;
                    } catch (Throwable $e) {
                        $failed++;

                        // Ограничиваем объем вывода, чтобы не захламлять консоль тысячами строк.
                        if (count($errors) < 20) {
                            $errors[] = sprintf(
                                'call_id=%d, entry_id=%s: %s',
                                (int) $call->id,
                                (string) $call->entry_id,
                                $e->getMessage(),
                            );
                        }
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("Скачано: {$downloaded}");
        $this->line("Пропущено (уже есть): {$skipped}");
        $this->line("Ошибок: {$failed}");

        if ($errors !== []) {
            $this->newLine();
            $this->warn('Первые ошибки:');
            foreach ($errors as $error) {
                $this->line(" - {$error}");
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function buildStoragePath(string $entryId): string
    {
        return 'calls/'.trim($entryId).'.mp3';
    }

    private function downloadRecording(Call $call): string
    {
        $response = Http::withOptions([
            'proxy' => '37.140.195.195:8888',
            'verify' => false,
            'timeout' => 180,
        ])
            ->retry(3, 1000, throw: false)
            ->get(Mango::buildRecordingLink((string) $call->recording, 'download'));

        if (! $response->successful()) {
            throw new RuntimeException(
                "Не удалось скачать запись (call_id={$call->id}, HTTP {$response->status()})"
            );
        }

        $body = $response->body();
        if ($body === '') {
            throw new RuntimeException("Получен пустой файл (call_id={$call->id})");
        }

        return $body;
    }
}
