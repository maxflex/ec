<?php

namespace App\Console\Commands\Once;

use App\Enums\TeacherStatus;
use App\Models\Teacher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublishTeachersCommand extends Command
{
    protected $signature = 'once:publish-teachers';

    protected $description = 'Command description';

    public function handle(): void
    {
        DB::table('teachers')->update([
            'is_published' => false
        ]);
       
        $teachers = Teacher::query()
            ->where('status', TeacherStatus::active)
            ->whereHas('photo')
            ->whereNotNull('photo_desc')
            ->get();

        $bar = $this->output->createProgressBar($teachers->count());
        foreach ($teachers as $teacher) {
            DB::table('teachers')->whereId($teacher->id)->update([
                'is_published' => true
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
