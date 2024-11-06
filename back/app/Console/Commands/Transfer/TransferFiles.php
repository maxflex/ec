<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TransferFiles extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:files';
    protected $description = 'Transfer files (for lessons)';

    public function handle()
    {
        DB::table('lessons')->update([
            'files' => null
        ]);

        // delete all files in the dir
        Storage::delete(Storage::files('crm/lessons'));

        $files = DB::connection('egecrm')
            ->table('files', 'f')
            ->join('lessons as l', 'l.id', '=', 'f.entity_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->where('f.entity_type', self::ET_LESSON)
            ->whereIn('g.year', [2023, 2024])
            ->selectRaw('f.*')
            ->get()
            ->groupBy('entity_id');

        $bar = $this->output->createProgressBar($files->count());
        foreach ($files as $lessonId => $lessonFiles) {
            $json = [];
            foreach ($lessonFiles as $lessonFile) {
                $file = file_get_contents("https://lk.ege-centr.ru/storage/img/upload/{$lessonFile->name}");
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
        Schema::enableForeignKeyConstraints();
    }
}
/**
 * [{"url": "https://cdn.ege-centr.ru/crm/lessons/6724bb6bd08bb.csv", "name": "query_result.csv", "size": 11462}]
 */
