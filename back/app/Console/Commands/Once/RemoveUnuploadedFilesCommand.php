<?php

namespace App\Console\Commands\Once;

use App\Models\Lesson;
use Illuminate\Console\Command;

class RemoveUnuploadedFilesCommand extends Command
{
    protected $signature = 'once:remove-unuploaded-files';

    protected $description = 'Command description';

    public function handle(): void
    {
        $lessonsWithFiles = Lesson::whereNotNull('files')->get();
        $bar = $this->output->createProgressBar($lessonsWithFiles->count());
        foreach ($lessonsWithFiles as $lesson) {
            $bar->advance();
        }
        $bar->finish();
    }
}
