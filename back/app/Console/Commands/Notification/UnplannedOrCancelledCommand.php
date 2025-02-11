<?php

namespace App\Console\Commands\Notification;

use App\Enums\LessonStatus;
use App\Enums\TelegramTemplate;
use App\Models\Lesson;
use App\Models\TelegramMessage;
use Illuminate\Console\Command;

class UnplannedOrCancelledCommand extends Command
{
    protected $signature = 'notification:unplanned-or-cancelled';

    protected $description = 'Рассылается преподавателям, ученикам и представителям в 20:00 предыдущим днем.';

    public function handle(): void
    {
        $tomorrow = now()->addDay();

        $lessons = Lesson::query()
            ->whereRaw('(`status` = ? OR `is_unplanned`)', [
                LessonStatus::cancelled,
            ])
            ->where('date', $tomorrow->format('Y-m-d'))
            ->get();

        $result = [
            'clients' => [],
            'parents' => [],
            'teachers' => [],
        ];

        foreach ($lessons as $lesson) {
            foreach ($lesson->group->clientGroups as $clientGroup) {
                $client = $clientGroup->contractVersionProgram->contractVersion->contract->client;
                $parent = $client->parent;
                if (! isset($result['clients'][$client->id])) {
                    $result['clients'][$client->id] = [
                        'receiver' => $client,
                        'lessons' => [],
                    ];
                    $result['parents'][$parent->id] = [
                        'receiver' => $parent,
                        'lessons' => [],
                    ];
                }
                $result['clients'][$client->id]['lessons'][] = $lesson;
                $result['parents'][$parent->id]['lessons'][] = $lesson;
            }

            $teacher = $lesson->teacher;
            // в отменённых препод может быть не установлен
            if ($teacher) {
                if (! isset($result['teachers'][$teacher->id])) {
                    $result['teachers'][$teacher->id] = [
                        'receiver' => $teacher,
                        'lessons' => [],
                    ];
                }
                $result['teachers'][$teacher->id]['lessons'][] = $lesson;
            }
        }

        foreach ($result as $key => $data) {
            $this->info($key);
            foreach ($data as $id => $d) {
                // $this->line("$id\t".count($d['lessons']));
                TelegramMessage::sendTemplate(
                    TelegramTemplate::unplannedOrCancelled,
                    $d['receiver'],
                    [
                        'tomorrow' => $tomorrow,
                        'receiver' => $d['receiver'],
                        'lessons' => $d['lessons'],
                        'key' => $key,
                    ]
                );
            }
            $this->line(PHP_EOL);
        }
    }
}
