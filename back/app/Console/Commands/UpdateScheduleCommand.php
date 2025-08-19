<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Group;
use App\Models\Teacher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateScheduleCommand extends Command
{
    protected $signature = 'app:update-schedule';

    protected $description = 'Пересчитать расписания всех сущностей';

    public function handle(): void
    {
        $this->clientSchedule();
        $this->teacherSchedule();
        $this->groupSchedule();
    }

    private function clientSchedule(): void
    {
        $this->info('Clients');

        DB::table('clients')->update(['schedule' => null]);
        $clients = Client::query()
            ->whereHas('contracts')
            ->with('contracts')
            ->get();

        $bar = $this->output->createProgressBar($clients->count());

        foreach ($clients as $client) {
            $schedule = [];
            foreach ($client->contracts->pluck('year')->unique() as $year) {
                $schedule[$year] = $client->getSchedule($year);
            }
            $client->schedule = $this->cleanUpSchedule($schedule);
            $client->save();
            $bar->advance();
        }
        $bar->finish();

        $this->line(PHP_EOL);
    }

    private function cleanUpSchedule($schedule)
    {
        $result = [];

        foreach ($schedule as $year => $teeth) {
            if (count((array) $teeth)) {
                $result[$year] = $teeth;
            }
        }

        if (count($result)) {
            return $result;
        }

        return null;
    }

    private function teacherSchedule(): void
    {
        $this->info('Teachers');

        DB::table('teachers')->update(['schedule' => null]);
        $teachers = Teacher::whereHas('lessons')->get();

        $bar = $this->output->createProgressBar($teachers->count());

        foreach ($teachers as $teacher) {
            $schedule = [];
            $years = Group::query()
                ->whereHas(
                    'lessons',
                    fn ($q) => $q->where('teacher_id', $teacher->id)
                )
                ->pluck('year')
                ->unique();

            foreach ($years as $year) {
                $schedule[$year] = $teacher->getSchedule($year);
            }
            $teacher->schedule = $this->cleanUpSchedule($schedule);
            $teacher->save();
            $bar->advance();
        }
        $bar->finish();

        $this->line(PHP_EOL);
    }

    private function groupSchedule(): void
    {
        $this->info('Groups');

        DB::table('groups')->update(['schedule' => null]);
        $groups = Group::whereHas('lessons')->get();

        $bar = $this->output->createProgressBar($groups->count());

        foreach ($groups as $group) {
            $group->updateSchedule($group->year);
            $bar->advance();
        }
        $bar->finish();

        $this->line(PHP_EOL);
    }
}
