<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class Photo extends Model
{
    // потому что string-id
    const DISABLE_LOGS = true;

    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';
    protected $fillable = [
        'entity_type', 'entity_id'
    ];

    public function getUrlAttribute()
    {
        return "https://cdn.ege-centr.ru/photos/{$this->id}.jpg";
    }

    public function upload(UploadedFile $file)
    {
        $file = Image::make($file)->fit(300)->stream('jpg');
        $name = join('/', ['photos', $this->id . ".jpg"]);
        return Storage::put($name, $file);
    }

    public function deleteFromCdn()
    {
        $name = join('/', ['photos', $this->id . ".jpg"]);
        Storage::delete($name);
    }

    public static function booted()
    {
        static::creating(function ($photo) {
            $photo->id = uniqid();
        });
        static::deleting(function ($photo) {
            $photo->deleteFromCdn();
        });
    }
}
