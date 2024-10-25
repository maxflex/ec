<?php

namespace App\Console\Commands\Transfer;

use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransferPhotos extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:photos';
    protected $description = 'Transfer photos';

    public function handle()
    {
        $oldPhotos = Photo::pluck('id');
        Storage::delete($oldPhotos->map(
            fn($id) => "crm/photos/{$id}.jpg"
        )->all());
        DB::table('photos')->truncate();
        $photos = DB::connection('egecrm')
            ->table('photos')
            ->where('has_cropped', 1)
            ->whereNotNull('entity_type')
            ->get();
        $bar = $this->output->createProgressBar($photos->count());
        foreach ($photos as $photo) {
            $file = file_get_contents("https://img.ege-centr.ru/avatar/{$photo->id}_cropped.jpg");
            $id = str()->random(20);
            Storage::put("crm/photos/{$id}.jpg", $file);
            DB::table('photos')->insert([
                'id' => $id,
                'entity_type' => $this->mapEntity($photo->entity_type),
                'entity_id' => $photo->entity_id,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
