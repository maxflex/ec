<?php

namespace App\Console\Commands\Once;

use App\Models\Call;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class FillMissingMangoRecordingsCommand extends Command
{
    private const WINDOW_DAYS = 30;

    private const STATS_FIELDS = 'records,start,finish,answer,from_number,to_number,line_number,entry_id';

    protected $signature = 'once:fill-missing-mango-recordings';

    protected $description = 'Проставляет calls.recording по данным Mango для всех звонков, где recording отсутствует';

    public function handle(): int
    {
        $baseQuery = $this->baseQuery();
        $totalMissing = (clone $baseQuery)->count();

        if ($totalMissing === 0) {
            $this->info('Готово: звонков с пустым recording нет.');

            return self::SUCCESS;
        }

        $this->line("Кандидатов в БД (answered_at != null и recording = null): {$totalMissing}");

        $candidateEntryIds = $this->loadCandidateEntryIds($baseQuery);
        if ($candidateEntryIds->isEmpty()) {
            $this->error('Не найдено ни одного entry_id для сверки с Mango.');

            return self::FAILURE;
        }

        $this->line('Кандидатов с entry_id: '.$candidateEntryIds->count());

        try {
            [$from, $to] = $this->resolvePeriodFromCandidates($baseQuery);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line(sprintf(
            'Период из БД-кандидатов: %s .. %s',
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s'),
        ));

        try {
            $recordingsByEntry = $this->loadMangoRecordingsByEntry($from, $to, $candidateEntryIds);
        } catch (Throwable $e) {
            $this->error("Ошибка запроса к Mango: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($recordingsByEntry->isEmpty()) {
            $this->error('В Mango не найдено ни одной записи для кандидатных entry_id.');

            return self::FAILURE;
        }

        $this->line('Найдено entry_id с записью в Mango: '.$recordingsByEntry->count());

        $updated = $this->applyRecordingUpdates($recordingsByEntry);
        $this->info("Обновлено строк в БД: {$updated}");

        $leftQuery = $this->baseQuery();
        $leftTotal = (clone $leftQuery)->count();
        $leftWithEntry = (clone $leftQuery)
            ->whereNotNull('entry_id')
            ->whereRaw("TRIM(entry_id) <> ''")
            ->count();

        if ($leftTotal === 0) {
            $this->info('Готово: recording заполнен для всех кандидатов.');

            return self::SUCCESS;
        }

        $this->warn("Осталось незаполненных кандидатов: {$leftTotal}");
        $this->warn("Из них с entry_id: {$leftWithEntry}");

        $this->table(
            ['id', 'entry_id', 'created_at', 'number'],
            (clone $leftQuery)
                ->select(['id', 'entry_id', 'created_at', 'number'])
                ->orderBy('created_at')
                ->limit(20)
                ->get()
                ->map(fn (Call $call): array => [
                    'id' => (string) $call->id,
                    'entry_id' => (string) $call->entry_id,
                    'created_at' => (string) $call->created_at,
                    'number' => (string) $call->number,
                ])
                ->all(),
        );

        // Возвращаем ошибку, чтобы сразу было видно, что задача "заполнить всё" не достигнута.
        return self::FAILURE;
    }

    /**
     * Базовый набор звонков, для которых мы обязаны заполнить recording.
     */
    private function baseQuery(): Builder
    {
        return Call::query()
            ->whereNotNull('answered_at')
            ->whereNull('recording');
    }

    /**
     * Получает уникальные entry_id из кандидатов.
     *
     * @return Collection<int, string>
     */
    private function loadCandidateEntryIds(Builder $baseQuery): Collection
    {
        return (clone $baseQuery)
            ->whereNotNull('entry_id')
            ->whereRaw("TRIM(entry_id) <> ''")
            ->pluck('entry_id')
            ->map(fn ($entryId): string => trim((string) $entryId))
            ->filter(fn (string $entryId): bool => $entryId !== '')
            ->unique()
            ->values();
    }

    /**
     * Возвращает диапазон дат из самих кандидатов в БД.
     *
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    private function resolvePeriodFromCandidates(Builder $baseQuery): array
    {
        $timezone = config('app.timezone', 'Europe/Moscow');
        $fromRaw = (clone $baseQuery)->min('created_at');
        $toRaw = (clone $baseQuery)->max('finished_at') ?? (clone $baseQuery)->max('created_at');

        if ($fromRaw === null || $toRaw === null) {
            throw new RuntimeException('Не удалось определить период по кандидатам из calls.');
        }

        $from = CarbonImmutable::parse((string) $fromRaw, $timezone);
        $to = CarbonImmutable::parse((string) $toRaw, $timezone);

        return [$from, $to];
    }

    /**
     * Возвращает map: entry_id => recording_id.
     * Если у entry_id несколько recording_id, берем первый.
     *
     * @param Collection<int, string> $candidateEntryIds
     * @return Collection<string, string>
     */
    private function loadMangoRecordingsByEntry(
        CarbonImmutable $from,
        CarbonImmutable $to,
        Collection $candidateEntryIds,
    ): Collection {
        $candidateLookup = array_fill_keys($candidateEntryIds->all(), true);
        /** @var array<string, string> $recordingsByEntry */
        $recordingsByEntry = [];
        $multiRecordEntries = 0;

        /** @var array<int, array{from: CarbonImmutable, to: CarbonImmutable}> $windows */
        $windows = $this->buildWindows($from, $to);
        $windowNo = 0;

        while ($windows !== []) {
            $window = array_shift($windows);
            if (! is_array($window)) {
                continue;
            }

            $windowFrom = $window['from'];
            $windowTo = $window['to'];
            $windowNo++;

            $this->line(sprintf(
                'Mango window #%d: %s .. %s',
                $windowNo,
                $windowFrom->format('Y-m-d H:i:s'),
                $windowTo->format('Y-m-d H:i:s'),
            ));

            try {
                $statsBody = $this->loadMangoStatsBody($windowFrom, $windowTo);
            } catch (Throwable $e) {
                // Если Mango отвечает 400, обычно это перегрузка по объему окна.
                // Дробим окно пополам до тех пор, пока не получим валидный ответ.
                $durationSeconds = $windowFrom->diffInSeconds($windowTo);
                $canSplit = str_contains($e->getMessage(), 'HTTP 400') && $durationSeconds > 1;

                if ($canSplit) {
                    $middle = $windowFrom->addSeconds((int) floor($durationSeconds / 2));
                    array_unshift($windows, ['from' => $middle->addSecond(), 'to' => $windowTo]);
                    array_unshift($windows, ['from' => $windowFrom, 'to' => $middle]);

                    $this->warn(sprintf(
                        'Mango вернул 400, делю окно: %s .. %s и %s .. %s',
                        $windowFrom->format('Y-m-d H:i:s'),
                        $middle->format('Y-m-d H:i:s'),
                        $middle->addSecond()->format('Y-m-d H:i:s'),
                        $windowTo->format('Y-m-d H:i:s'),
                    ));

                    continue;
                }

                throw $e;
            }

            if ($statsBody === '') {
                continue;
            }

            $lines = preg_split('/\r\n|\n|\r/', $statsBody) ?: [];
            foreach ($lines as $line) {
                if ($line === '') {
                    continue;
                }

                // Формат строки фиксированный:
                // records;start;finish;answer;from_number;to_number;line_number;entry_id
                $cells = str_getcsv($line, ';');
                $cells = array_pad($cells, 8, '');

                $recordsRaw = trim((string) ($cells[0] ?? ''));
                $entryId = trim((string) ($cells[7] ?? ''));

                if ($entryId === '' || ! isset($candidateLookup[$entryId])) {
                    continue;
                }

                $recordingIds = $this->extractRecordingIds($recordsRaw);
                if ($recordingIds === []) {
                    continue;
                }

                if (count($recordingIds) > 1) {
                    $multiRecordEntries++;
                }

                // В нашей схеме хранится один recording_id, поэтому берем первый.
                if (! isset($recordingsByEntry[$entryId])) {
                    $recordingsByEntry[$entryId] = $recordingIds[0];
                }
            }
        }

        if ($multiRecordEntries > 0) {
            $this->warn("Entry c несколькими recording_id в Mango: {$multiRecordEntries}");
        }

        return collect($recordingsByEntry);
    }

    /**
     * Строит интервалы выгрузки в Mango.
     *
     * @return array<int, array{from: CarbonImmutable, to: CarbonImmutable}>
     */
    private function buildWindows(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $windows = [];
        $cursorFrom = $from;

        while ($cursorFrom->lessThanOrEqualTo($to)) {
            $cursorTo = $cursorFrom->addDays(self::WINDOW_DAYS)->subSecond();
            if ($cursorTo->greaterThan($to)) {
                $cursorTo = $to;
            }

            $windows[] = ['from' => $cursorFrom, 'to' => $cursorTo];
            $cursorFrom = $cursorTo->addSecond();
        }

        return $windows;
    }

    /**
     * Проставляет recording в calls.
     *
     * @param Collection<string, string> $recordingsByEntry
     */
    private function applyRecordingUpdates(Collection $recordingsByEntry): int
    {
        $updated = 0;
        $entryIds = $recordingsByEntry->keys()->values();

        // Разбиваем whereIn на чанки, чтобы не упираться в лимит плейсхолдеров SQL.
        foreach ($entryIds->chunk(1000) as $entryChunk) {
            /** @var Collection<int, Call> $calls */
            $calls = Call::query()
                ->select(['id', 'entry_id'])
                ->whereNotNull('answered_at')
                ->whereNull('recording')
                ->whereIn('entry_id', $entryChunk->all())
                ->get();

            foreach ($calls as $call) {
                $entryId = trim((string) $call->entry_id);
                $recordingId = (string) $recordingsByEntry->get($entryId, '');
                if ($recordingId === '') {
                    continue;
                }

                // Обновляем через Query Builder, чтобы не запускать массово observer/джобы.
                $affected = DB::table('calls')
                    ->where('id', $call->id)
                    ->whereNull('recording')
                    ->update(['recording' => $recordingId]);

                $updated += $affected;
            }
        }

        return $updated;
    }

    /**
     * Загружает CSV-строку статистики Mango для конкретного окна дат.
     */
    private function loadMangoStatsBody(CarbonImmutable $from, CarbonImmutable $to): string
    {
        $payload = [
            'date_from' => (string) $from->timestamp,
            'date_to' => (string) $to->timestamp,
            'fields' => self::STATS_FIELDS,
        ];

        $requestResponse = $this->mangoPost('https://app.mango-office.ru/vpbx/stats/request', $payload);
        if ($requestResponse->failed()) {
            throw new RuntimeException($this->formatMangoHttpError('stats/request', $requestResponse));
        }

        $key = (string) $requestResponse->json('key');
        if ($key === '') {
            throw new RuntimeException('Mango не вернул key в ответе stats/request.');
        }

        // Stats в Mango может готовиться не сразу, поэтому повторяем stats/result при HTTP 204.
        for ($attempt = 1; $attempt <= 15; $attempt++) {
            $resultResponse = $this->mangoPost('https://app.mango-office.ru/vpbx/stats/result', ['key' => $key]);

            if ($resultResponse->status() === 204) {
                sleep(2);
                continue;
            }

            if ($resultResponse->failed()) {
                throw new RuntimeException($this->formatMangoHttpError('stats/result', $resultResponse));
            }

            return trim((string) $resultResponse->body());
        }

        throw new RuntimeException('Mango не успел подготовить stats/result (после 15 попыток).');
    }

    /**
     * Формирует подробный текст ошибки HTTP-ответа Mango.
     */
    private function formatMangoHttpError(string $method, Response $response): string
    {
        $body = trim((string) $response->body());
        if ($body === '') {
            return "{$method} вернул HTTP {$response->status()}";
        }

        // Ограничиваем длину, чтобы лог в консоли не становился чрезмерным.
        $body = mb_substr($body, 0, 500);

        return "{$method} вернул HTTP {$response->status()}: {$body}";
    }

    /**
     * Разбирает поле records из stats/result в список recording_id.
     *
     * @return array<int, string>
     */
    private function extractRecordingIds(string $recordsRaw): array
    {
        $recordsRaw = trim($recordsRaw);
        if ($recordsRaw === '' || $recordsRaw === '[]' || strtolower($recordsRaw) === 'null') {
            return [];
        }

        if (str_starts_with($recordsRaw, '[') && str_ends_with($recordsRaw, ']')) {
            $decoded = json_decode($recordsRaw, true);
            if (is_array($decoded)) {
                return collect($decoded)
                    ->map(fn ($item): string => trim((string) $item))
                    ->filter(fn (string $item): bool => $item !== '')
                    ->values()
                    ->all();
            }

            // Fallback на случай "псевдо-массива" без корректного JSON.
            $recordsRaw = trim($recordsRaw, '[]');
        }

        return collect(explode(',', $recordsRaw))
            ->map(fn (string $item): string => trim($item, " \t\n\r\0\x0B\"'"))
            ->filter(fn (string $item): bool => $item !== '')
            ->values()
            ->all();
    }

    /**
     * Унифицированный POST-запрос в Mango с подписью.
     */
    private function mangoPost(string $url, array $payload): Response
    {
        $apiKey = (string) config('mango.api_key');
        $apiSalt = (string) config('mango.api_salt');

        if ($apiKey === '' || $apiSalt === '') {
            throw new RuntimeException('Не настроены MANGO_API_KEY и/или MANGO_API_SALT.');
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($json)) {
            throw new RuntimeException('Не удалось сериализовать payload для Mango.');
        }

        $sign = hash('sha256', $apiKey.$json.$apiSalt);

        return Http::asForm()
            ->timeout(20)
            ->connectTimeout(10)
            ->post($url, [
                'vpbx_api_key' => $apiKey,
                'sign' => $sign,
                'json' => $json,
            ]);
    }
}
