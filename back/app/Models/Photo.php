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
        'entity_type', 'entity_id',
    ];

    /**
     * Свободная загрузка.
     * Используется в загрузке фоток в инструкции
     */
    public static function arbitraryUpload(UploadedFile $file)
    {
        $file = Image::make($file)->stream('jpg');
        $fileName = uniqid('instruction_').'.jpg';
        Storage::put(implode('/', ['crm', 'photos', $fileName]), $file);

        return cdn('photos', $fileName);
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

    public function deleteFromCdn()
    {
        $name = implode('/', ['crm', 'photos', $this->id.'.jpg']);
        Storage::delete($name);
    }

    public function getUrlAttribute(): string
    {
        return cdn('photos', $this->id.'.jpg');
    }

    public function upload(UploadedFile $file)
    {
        $file = Image::make($file)->fit(300)->stream('jpg');
        $name = implode('/', ['crm', 'photos', $this->id.'.jpg']);

        return Storage::put($name, $file);
    }
}
