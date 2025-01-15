<?php

namespace App\Console\Commands\Transfer;

use App\Models\Lesson;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransferFiles extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:files';
    protected $description = 'Transfer files (for lessons)';

    public function handle()
    {
        $this->deleteExistingFiles();
        $this->uploadFilesFromV2();
    }

    /**
     * удалить ранее загруженные фалы (нужно для перезапуска команды)
     */
    private function deleteExistingFiles()
    {
        $this->info("Removing existing files...");
        $this->line(PHP_EOL);

        $query = Lesson::query()
            ->where('date', '<=', '2024-12-15')
            ->whereNotNull('files');

        $lessonsWithFiles = $query->get();

        $bar = $this->output->createProgressBar($lessonsWithFiles->count());

        foreach ($lessonsWithFiles as $lesson) {
            foreach ($lesson->files as $file) {
                if (isset($file->url)) {
                    $file = str($file->url)->after('https://cdn.ege-centr.ru/')->value();
                    Storage::delete($file);
                }
            }
            $bar->advance();
        }

        $bar->finish();

        $query->update(['files' => null]);
    }


    private function uploadFilesFromV2()
    {
        $this->info("Uploading files...");
        $this->line(PHP_EOL);


        $files = DB::connection('egecrm')
            ->table('files', 'f')
            ->join('lessons as l', 'l.id', '=', 'f.entity_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->where('f.entity_type', self::ET_LESSON)
            ->whereIn('g.year', [2023, 2024])
            ->where('l.date', '<=', '2024-12-15')
            ->selectRaw('f.*')
            ->get()
            ->groupBy('entity_id');

        $bar = $this->output->createProgressBar($files->count());
        foreach ($files as $lessonId => $lessonFiles) {
            $json = [];
            foreach ($lessonFiles as $lessonFile) {
                $file = file_get_contents("https://v2.ege-centr.ru/storage/img/upload/{$lessonFile->name}");
                Storage::put("crm/lessons/{$lessonFile->name}", $file);
                $json[] = [
                    'url' => cdn('lessons', $lessonFile->name),
                    'name' => $lessonFile->original_name,
                    'size' => strlen($file)
                ];
            }
            DB::table('lessons')->whereId($lessonId)->update([
                'files' => json_encode($json)
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
/**
 * [{"url": "https://cdn.ege-centr.ru/crm/lessons/6724bb6bd08bb.csv", "name": "query_result.csv", "size": 11462}]
 */
