<?php

namespace App\Traits;

use App\Models\Photo;

trait HasPhoto
{
    public function photo()
    {
        return $this->morphOne(Photo::class, 'entity');
    }

    public function getPhotoUrlAttribute()
    {
        $photo = $this->photo;
        if ($photo === null) {
            return null;
        }
        return "https://cdn.ege-centr.ru/photos/{$photo->id}.jpg";
    }
}
