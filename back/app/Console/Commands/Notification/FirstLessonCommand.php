<?php

namespace App\Console\Commands\Notification;

namespace App\Console\Commands\Notification;

use App\Enums\Cabinet;
use App\Enums\LessonStatus;
use App\Enums\TelegramTemplate;
use App\Models\Client;
use App\Models\Lesson;
use App\Models\Representative;
use App\Models\TelegramMessage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FirstLessonCommand extends Command
{
    // php artisan notification:first-lesson {mode} [--dry]
    protected $signature = 'notification:first-lesson {mode : One of 2d|1d|20min}';

    protected $description = 'Уведомления о первом занятии: 2d / 1d / 20min';

    /** @var Collection<int, object{client_id: int, first_lesson: string}> */
    private Collection $firstLessons;

    public function handle(): int
    {
        $mode = $this->argument('mode');

        if (! in_array($mode, ['2d', '1d', '20min'])) {
            $this->error('Invalid mode. Use one of: 2d | 1d | 20min');

            return self::INVALID;
        }

        $this->info('Getting first lessons...');
        $this->firstLessons = $this->getFirstLessons();

        return match ($mode) {
            '2d' => $this->firstLessonDayAfterTomorrow(),
            '1d' => $this->firstLessonTomorrow(),
            '20min' => $this->firstLesson20min(),
        };
    }

    /**
     * @return Collection<int, object{client_id: int, first_lesson: string}>
     */
    private function getFirstLessons(): Collection
    {
        $result = DB::select('
        WITH had_lessons AS (
            SELECT DISTINCT c.client_id
            FROM client_lessons cl
            JOIN lessons l        ON l.id = cl.lesson_id
            JOIN contract_version_programs cvp ON cvp.id = cl.contract_version_program_id
            JOIN contract_versions cv ON cv.id = cvp.contract_version_id
            JOIN contracts c       ON c.id = cv.contract_id AND c.year = ?
        ),
        candidates AS (
            SELECT
                c.client_id,
                l.date,
                l.time,
                l.cabinet,
                ROW_NUMBER() OVER (
                    PARTITION BY c.client_id
                    ORDER BY l.date ASC, l.time ASC
                ) AS rn
            FROM client_groups cg
            JOIN contract_version_programs cvp ON cvp.id = cg.contract_version_program_id
            JOIN contract_versions cv ON cv.id = cvp.contract_version_id
            JOIN contracts c ON c.id = cv.contract_id
            JOIN `groups` g  ON g.id = cg.group_id AND g.year = ?
            JOIN lessons l   ON l.group_id = cg.group_id AND l.status = ?
            WHERE c.client_id NOT IN (SELECT * FROM had_lessons)
        )
        SELECT * FROM candidates
        WHERE rn = 1;
        ', [
            current_academic_year(),
            current_academic_year(),
            LessonStatus::planned->value,
        ]);

        return collect($result);
    }

    /**
     * За 2 дня (ученикам и представителям)
     */
    private function firstLessonDayAfterTomorrow()
    {
        $template = TelegramTemplate::firstLessonDayAfterTomorrow;

        // $targetDate = is_localhost()
        //     ? '2025-09-09'
        //     : now()->addDays(2)->format('Y-m-d');
        $targetDate = now()->addDays(2)->format('Y-m-d');

        $firstLessons = $this->firstLessons->where('date', $targetDate);

        $this->info($template->value.'. First lessons: '.$firstLessons->count());

        $cnt = 0;
        $bar = $this->output->createProgressBar($firstLessons->count());
        foreach ($firstLessons as $firstLesson) {
            $client = Client::find($firstLesson->client_id);
            /** @var Representative $representative */
            $representative = $client->representative;

            // расписание ученика на этот день
            $lessons = Lesson::query()
                ->where('status', LessonStatus::planned)
                ->whereHas(
                    'group.clientGroups.contractVersionProgram.contractVersion.contract',
                    fn ($q) => $q->where('client_id', $client->id)
                )
                ->where('date', $firstLesson->date)
                ->orderBy('time')
                ->get();

            $phones = [
                ...$client->phones()->withTelegram()->get()->all(),
                ...$representative->phones()->withTelegram()->get()->all(),
            ];

            foreach ($phones as $phone) {
                TelegramMessage::sendTemplate($template, $phone, [
                    'person' => $phone->entity_type === Client::class ? $client : $representative,
                    'date' => Carbon::parse($firstLesson->date)->translatedFormat('j F'),
                    'lessons' => $lessons,
                ]);
                $cnt++;
                if (is_localhost() && $cnt >= 10) {
                    $bar->finish();

                    return self::SUCCESS;
                }
            }

            $bar->advance();
        }
        $bar->finish();

        return self::SUCCESS;
    }

    /**
     * За сутки (только представителям)
     */
    private function firstLessonTomorrow()
    {
        $template = TelegramTemplate::firstLessonTomorrow;

        // $targetDate = is_localhost()
        //     ? '2025-09-09'
        //     : now()->addDay()->format('Y-m-d');
        $targetDate = now()->addDay()->format('Y-m-d');

        $firstLessons = $this->firstLessons->where('date', $targetDate);

        $this->info($template->value.'. First lessons: '.$firstLessons->count());

        $cnt = 0;
        $bar = $this->output->createProgressBar($firstLessons->count());
        foreach ($firstLessons as $firstLesson) {
            $client = Client::find($firstLesson->client_id);
            /** @var Representative $representative */
            $representative = $client->representative;

            $phones = $representative->phones()->withTelegram()->get();

            foreach ($phones as $phone) {
                TelegramMessage::sendTemplate($template, $phone, [
                    'person' => $representative,
                ]);
                $cnt++;
                if (is_localhost() && $cnt >= 3) {
                    $bar->finish();

                    return self::SUCCESS;
                }
            }

            $bar->advance();
        }
        $bar->finish();

        return self::SUCCESS;
    }

    /**
     * За 20 минут
     */
    private function firstLesson20min()
    {
        $template = TelegramTemplate::firstLesson20min;

        // $targetDate = is_localhost() ? '2025-09-09' : now()->format('Y-m-d');
        // $targetTime = is_localhost() ? '10:20:00' : now()->addMinutes(20)->format('H:i:00');
        $targetDate = now()->format('Y-m-d');
        $targetTime = now()->addMinutes(20)->format('H:i:00');

        $firstLessons = $this->firstLessons
            ->where('date', $targetDate)
            ->where('time', $targetTime);

        $this->info($template->value.'. First lessons: '.$firstLessons->count());

        $cnt = 0;
        $bar = $this->output->createProgressBar($firstLessons->count());
        foreach ($firstLessons as $firstLesson) {
            $cabinet = Cabinet::from($firstLesson->cabinet);
            $client = Client::find($firstLesson->client_id);
            /** @var Representative $representative */
            $representative = $client->representative;

            $phones = $representative->phones()->withTelegram()->get();

            foreach ($phones as $phone) {
                TelegramMessage::sendTemplate($template, $phone, [
                    'person' => $representative,
                    'cabinet' => $cabinet,
                ]);
                $cnt++;
                if (is_localhost() && $cnt >= 3) {
                    $bar->finish();

                    return self::SUCCESS;
                }
            }
            $bar->advance();
        }
        $bar->finish();

        return self::SUCCESS;
    }
}
