<?php

namespace App\Console\Commands\Once;

use App\Enums\Direction;
use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Models\Client;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExtendGroupsLessonsToMayCommand extends Command
{
    private const AUTHOR_USER_ID = 5;

    protected $signature = 'once:extend-groups-lessons-to-may
        {--dry : Только показать, какие занятия будут созданы}';

    protected $description = 'Продлить занятия в группах школа-8/9/10/11 и экстернат до конца мая в текущем режиме';

    public function handle(): void
    {
        $year = current_academic_year();
        $dry = (bool) $this->option('dry');

        $targetPrograms = collect(Program::cases())
            ->filter(fn (Program $program) => in_array($program->getDirection(), [
                Direction::school8,
                Direction::school9,
                Direction::school10,
                Direction::school11,
                Direction::external,
            ], true))
            ->map(fn (Program $program) => $program->value)
            ->values();

        $groups = Group::query()
            ->where('year', $year)
            ->whereIn('program', $targetPrograms)
            ->get();

        $this->info(sprintf(
            'Год: %d. Групп в выборке: %d. Режим: %s',
            $year,
            $groups->count(),
            $dry ? 'dry-run' : 'create'
        ));

        $mayEnd = Carbon::create($year + 1, 5, 31);
        $affectedGroupIds = collect();
        $affectedTeacherIds = collect();
        $createdLessonIds = [];
        $createdLessonsCount = 0;
        $eligibleGroupsCount = 0;

        $bar = $this->output->createProgressBar($groups->count());

        foreach ($groups as $group) {
            $lastLesson = $this->getLastRegularLesson($group->id);
            if (! $lastLesson) {
                $bar->advance();
                continue;
            }

            $lastLessonDate = Carbon::parse($lastLesson->date);
            if (! in_array($lastLessonDate->month, [4, 5], true)) {
                $bar->advance();
                continue;
            }

            if ($lastLessonDate->gte($mayEnd)) {
                $bar->advance();
                continue;
            }

            $slots = collect($group->getSchedule($year));
            if ($slots->isEmpty()) {
                $bar->advance();
                continue;
            }

            // Для каждого слота подбираем последний реальный урок как шаблон атрибутов.
            $templatesBySlot = $this->getTemplatesBySlot($group->id, $slots);
            if (count($templatesBySlot) === 0) {
                $bar->advance();
                continue;
            }

            $rows = $this->buildLessonsRows(
                $group->id,
                $lastLessonDate->copy()->addDay(),
                $mayEnd,
                $slots->all(),
                $templatesBySlot
            );

            if (count($rows) === 0) {
                $bar->advance();
                continue;
            }

            $eligibleGroupsCount++;
            $createdLessonsCount += count($rows);

            if ($dry) {
                $this->line(sprintf(
                    'group_id=%d, программа=%s, последнее=%s, будет создано=%d',
                    $group->id,
                    $group->program->value,
                    $lastLessonDate->format('Y-m-d'),
                    count($rows)
                ));
                $bar->advance();
                continue;
            }

            foreach ($rows as $row) {
                $createdLessonIds[] = DB::table('lessons')->insertGetId($row);
            }

            $affectedGroupIds->push($group->id);
            foreach ($rows as $row) {
                if ($row['teacher_id']) {
                    $affectedTeacherIds->push($row['teacher_id']);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (! $dry) {
            $this->updateComputedSchedules(
                $year,
                $affectedGroupIds->unique()->values(),
                $affectedTeacherIds->unique()->values()
            );
        }

        $this->info(sprintf(
            'Готово. Групп с продлением: %d. Новых занятий: %d.',
            $eligibleGroupsCount,
            $createdLessonsCount
        ));

        if (! $dry) {
            $this->line('ID созданных занятий: '.(count($createdLessonIds) ? implode(', ', $createdLessonIds) : 'нет'));
        }
    }

    private function getLastRegularLesson(int $groupId): ?Lesson
    {
        return Lesson::query()
            ->where('group_id', $groupId)
            ->where('is_unplanned', false)
            ->where('status', '<>', LessonStatus::cancelled)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->first();
    }

    /**
     * @param  array<int, array{weekday: int, time: string}>  $slots
     * @return array<string, Lesson>
     */
    private function getTemplatesBySlot(int $groupId, $slots): array
    {
        $lessons = Lesson::query()
            ->where('group_id', $groupId)
            ->where('is_unplanned', false)
            ->where('status', '<>', LessonStatus::cancelled)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->get();

        $result = [];
        foreach ($slots as $slot) {
            $key = $this->slotKey(intval($slot['weekday']), $slot['time']);
            $template = $lessons->first(function (Lesson $lesson) use ($slot) {
                return $lesson->time === $slot['time']
                    && Carbon::parse($lesson->date)->dayOfWeekIso - 1 === intval($slot['weekday']);
            });

            if ($template) {
                $result[$key] = $template;
            }
        }

        return $result;
    }

    /**
     * @param  array<int, array{weekday: int, time: string}>  $slots
     * @param  array<string, Lesson>  $templatesBySlot
     * @return array<int, array<string, mixed>>
     */
    private function buildLessonsRows(
        int $groupId,
        Carbon $dateFrom,
        Carbon $dateTo,
        array $slots,
        array $templatesBySlot
    ): array {
        $existingLessons = Lesson::query()
            ->where('group_id', $groupId)
            ->whereBetween('date', [$dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')])
            ->get(['date', 'time'])
            ->groupBy('date')
            ->map(fn ($items) => $items->pluck('time')->values()->all());

        $now = now();
        $rows = [];
        $cursor = $dateFrom->copy();

        while ($cursor->lte($dateTo)) {
            $weekday = $cursor->dayOfWeekIso - 1; // 0 = Пн ... 6 = Вс
            $date = $cursor->format('Y-m-d');
            $existingTimes = $existingLessons->get($date, []);

            foreach ($slots as $slot) {
                if (intval($slot['weekday']) !== $weekday) {
                    continue;
                }

                if (in_array($slot['time'], $existingTimes, true)) {
                    continue;
                }

                $slotKey = $this->slotKey($weekday, $slot['time']);
                $template = $templatesBySlot[$slotKey] ?? null;
                if (! $template) {
                    continue;
                }

                $rows[] = [
                    'group_id' => $groupId,
                    'teacher_id' => $template->teacher_id,
                    'price' => $template->price,
                    'status' => LessonStatus::planned->value,
                    'cabinet' => $template->cabinet?->value,
                    'quarter' => $template->quarter,
                    'date' => $date,
                    'time' => $slot['time'],
                    'conducted_at' => null,
                    'is_free' => $template->is_free,
                    'is_unplanned' => false,
                    'is_topic_verified' => false,
                    'topic' => null,
                    'homework' => null,
                    'files' => null,
                    'user_id' => self::AUTHOR_USER_ID,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $existingTimes[] = $slot['time'];
            }

            $cursor->addDay();
        }

        return $rows;
    }

    private function slotKey(int $weekday, string $time): string
    {
        return $weekday.'|'.$time;
    }

    private function updateComputedSchedules(int $year, $groupIds, $teacherIds): void
    {
        if ($groupIds->isEmpty()) {
            return;
        }

        $this->info('Обновляю сохранённые расписания (group/teacher/client)...');

        $groups = Group::query()
            ->whereIn('id', $groupIds)
            ->with('clientGroups.contractVersionProgram.contractVersion.contract')
            ->get();

        $clientIds = collect();
        foreach ($groups as $group) {
            $group->updateSchedule($year);

            foreach ($group->clientGroups as $clientGroup) {
                $clientId = $clientGroup
                    ->contractVersionProgram
                    ?->contractVersion
                    ?->contract
                    ?->client_id;

                if ($clientId) {
                    $clientIds->push($clientId);
                }
            }
        }

        Teacher::query()
            ->whereIn('id', $teacherIds)
            ->get()
            ->each(fn (Teacher $teacher) => $teacher->updateSchedule($year));

        Client::query()
            ->whereIn('id', $clientIds->unique()->values())
            ->get()
            ->each(fn (Client $client) => $client->updateSchedule($year));
    }
}
