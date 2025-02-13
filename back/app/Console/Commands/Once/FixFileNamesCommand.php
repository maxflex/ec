<?php

namespace App\Console\Commands\Once;

use App\Models\Lesson;
use Exception;
use Illuminate\Console\Command;

class FixFileNamesCommand extends Command
{
    protected $signature = 'once:fix-file-names';

    protected $description = 'Исправляет названия файлов в lessons с поехавшей кодировкой';

    public function handle(): void
    {
        // $lessons = Lesson::whereIn('id', [83973, 83974, 83975])->get();
        $lessons = Lesson::whereNotNull('files')->get();

        $bar = $this->output->createProgressBar(count($lessons));
        foreach ($lessons as $lesson) {
            $files = $lesson->files;
            foreach ($files as $file) {
                $file->name = $this->fixEncoding($file->name);
            }
            Lesson::whereId($lesson->id)->update(['files' => json_encode($files)]);
            $bar->advance();
        }
        $bar->finish();
    }

    private function fixEncoding(string $text)
    {
        try {
            // Reverse the incorrect ISO-8859-1 -> UTF-8 encoding
            return iconv('UTF-8', 'ISO-8859-1', $text);
        } catch (Exception $e) {
            return $text;
        }
    }
}
