<?php

namespace App\Console\Commands\Notification;

namespace App\Console\Commands\Notification;

use App\Enums\TelegramTemplate;
use App\Models\Lesson;
use App\Models\TelegramMessage;
use Exception;
use Illuminate\Console\Command;

class FirstLessonTomorrowCommand extends Command
{
    protected $signature = 'notification:first-lesson-tomorrow';

    protected $description = 'Уведомления о первом занятии (за 1 день и за 2 дня)';

    public function handle(): int
    {
        if (is_localhost()) {
            $tomorrow = '2025-09-09';
            $theDayAfterTomorrow = '2025-09-10';
        } else {
            $tomorrow = now()->addDay()->format('Y-m-d');
            $theDayAfterTomorrow = now()->addDays(2)->format('Y-m-d');
        }

        // set для уже отосланных (ключи: c{client_id}, r{rep_id})
        $sent = [];

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

        foreach ($firstLessons as $firstLesson) {
            [$date, $time] = explode(' ', $firstLesson->dt);

            if ($date !== $tomorrow && $date !== $theDayAfterTomorrow) {
                continue;
            }

            $template = $date === $tomorrow
                ? TelegramTemplate::firstLessonTomorrow
                : TelegramTemplate::firstLessonDayAfterTomorrow;

            // (опционально) за 2 дня — ученики + представители; за 1 день — только ученики
            $sendRepresentatives = ($date === $theDayAfterTomorrow);

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
                if (count($sent) >= 10) {
                    return self::SUCCESS;
                }

                $client = $clientGroup->contractVersionProgram->contractVersion->contract->client;
                $lessons = Lesson::query()
                    ->whereHas(
                        'group.clientGroups.contractVersionProgram.contractVersion.contract',
                        fn ($q) => $q->where('client_id', $client->id)
                    )
                    ->where('date', $date)
                    ->orderBy('time', 'asc')
                    ->get();

                // === Клиент ===
                $key = 'c'.$client->id;
                if (! isset($sent[$key])) {
                    // отправляем всем его телеграм-номерам (если их несколько)
                    foreach ($client->phones()->withTelegram()->get() as $phone) {
                        TelegramMessage::sendTemplate($template, $phone, [
                            'person' => $client,
                            'date' => $date,
                            'lessons' => $lessons,
                        ]);
                    }
                    $sent[$key] = true; // помечаем как отправлено
                }

                // === Представитель ===
                if (! $sendRepresentatives) {
                    continue;
                }

                $representative = $client->representative;
                $key = 'r'.$representative->id;
                if (! isset($sent[$key])) {
                    foreach ($representative->phones()->withTelegram()->get() as $phone) {
                        TelegramMessage::sendTemplate($template, $phone, [
                            'person' => $representative,
                            'date' => $date,
                            'lessons' => $lessons,
                        ]);
                    }
                    $sent[$key] = true;
                }

            }
        }

        return self::SUCCESS;
    }
}
