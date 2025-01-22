<?php

namespace App\Console\Commands\Once;

use App\Models\Lesson;
use Illuminate\Console\Command;

class RemoveNotUploadedFilesCommand extends Command
{
    protected $signature = 'once:remove-not-uploaded-files';

    protected $description = 'Command description';

    public function handle(): void
    {
        $lessonsWithFiles = Lesson::whereNotNull('files')->get();
        $bar = $this->output->createProgressBar($lessonsWithFiles->count());
        $result = collect();
        foreach ($lessonsWithFiles as $lesson) {
            foreach ($lesson->files as $file) {
                if (!isset($file->url)) {
                    $result->push($lesson);
                    continue 2;
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->line(PHP_EOL);
        foreach ($result->sortBy('date')->values() as $r) {
            $this->line(implode("\t", [
                $r->id,
                $r->date,
            ]));
        }
    }
}
