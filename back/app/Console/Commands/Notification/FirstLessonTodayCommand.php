<?php

namespace App\Console\Commands\Notification;

namespace App\Console\Commands\Notification;

use App\Enums\TelegramTemplate;
use App\Models\Lesson;
use App\Models\TelegramMessage;
use Exception;
use Illuminate\Console\Command;

class FirstLessonTodayCommand extends Command
{
    protected $signature = 'notification:first-lesson-today';

    protected $description = 'Уведомления о первом занятии, отправляется ученику за 20 мин до начала первого занятия';

    public function handle(): int
    {
        $targetDateTime = is_localhost()
            ? '2025-09-09 10:20:00'
            : now()->addMinutes(20)->format('Y-m-d H:i:00');

        $firstLessons = Lesson::selectRaw("
                MIN(CONCAT(lessons.`date`, ' ', lessons.`time`)) as `dt`,
                lessons.group_id
            ")
            ->join('groups as g', fn ($join) => $join
                ->on('g.id', '=', 'lessons.group_id')
                ->where('g.year', current_academic_year())
            )
            ->groupBy('lessons.group_id')
            ->get();

        $sent = 0;

        foreach ($firstLessons as $firstLesson) {
            if ($firstLesson->dt !== $targetDateTime) {
                continue;
            }

            [$date, $time] = explode(' ', $firstLesson->dt);

            $lesson = Lesson::query()
                ->with([
                    'group.clientGroups.contractVersionProgram.contractVersion.contract.client.phones',
                    'group.clientGroups.contractVersionProgram.contractVersion.contract.client.representative.phones',
                ])
                ->where('group_id', $firstLesson->group_id)
                ->where('date', $date)
                ->where('time', $time)
                ->first();

            if (! $lesson) {
                throw new Exception('lesson not found at '.$firstLesson->dt.' group_id='.$firstLesson->group_id);
            }

            foreach ($lesson->group->clientGroups as $clientGroup) {
                $client = $clientGroup->contractVersionProgram->contractVersion->contract->client;
                // отправляем всем его телеграм-номерам (если их несколько)
                foreach ($client->phones()->withTelegram()->get() as $phone) {
                    TelegramMessage::sendTemplate(TelegramTemplate::firstLessonToday, $phone, [
                        'person' => $client,
                        'date' => $date,
                        'lesson' => $lesson,
                    ]);
                }
                $sent++;

                if ($sent >= 5) {
                    return self::SUCCESS;
                }

            }
        }

        return self::SUCCESS;
    }
}
