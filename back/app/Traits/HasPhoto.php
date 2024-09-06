<?php

namespace App\Traits;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasPhoto
{
    public function photo(): MorphOne
    {
        return $this->morphOne(Photo::class, 'entity');
    }

    public function getPhotoUrlAttribute()
    {
        return optional($this->photo)->url;
    }
}
