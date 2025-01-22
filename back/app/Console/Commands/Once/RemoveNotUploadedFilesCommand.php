<?php

namespace App\Console\Commands\Once;

use App\Models\Lesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveNotUploadedFilesCommand extends Command
{
    protected $signature = 'once:remove-not-uploaded-files';

    protected $description = 'Command description';

    public function handle(): void
    {
        $lessonsWithFiles = Lesson::whereNotNull('files')->get();
        $bar = $this->output->createProgressBar($lessonsWithFiles->count());
        foreach ($lessonsWithFiles as $lesson) {
            $newFiles = collect($lesson->files)
                ->filter(fn($f) => isset($f->url))
                ->values()
                ->all();

            DB::table('lessons')->whereId($lesson->id)->update([
                'files' => count($newFiles) ? json_encode($newFiles) : null
            ]);

            $bar->advance();
        }

    }
}
