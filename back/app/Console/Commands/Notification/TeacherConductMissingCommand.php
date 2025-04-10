<?php

namespace App\Console\Commands\Notification;

use App\Enums\LessonStatus;
use App\Enums\TelegramTemplate;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\TelegramMessage;
use Illuminate\Console\Command;

class TeacherConductMissingCommand extends Command
{
    protected $signature = 'notification:teacher-conduct-missing';

    protected $description = 'Напоминание преподу за незаполненную проводку';

    public function handle(): void
    {
        $data = Lesson::query()
            ->where('status', LessonStatus::planned)
            ->where('date', now()->subDay()->format('Y-m-d'))
            ->get()
            ->groupBy('teacher_id');

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $teacherId => $lessons) {
            $teacher = Teacher::find($teacherId);
            TelegramMessage::sendTemplate(
                TelegramTemplate::teacherConductMissing,
                $teacher,
                [
                    'teacher' => $teacher,
                    'lessons' => $lessons,
                ]
            );
            $bar->advance();
        }
        $bar->finish();
    }
}
