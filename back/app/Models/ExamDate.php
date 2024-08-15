<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamDate extends Model
{
    public $timestamps = false;

    protected $fillable = ['dates'];

    public function getDatesAttribute($value)
    {
        if ($value === null) {
            return [];
        }
        $year = current_academic_year();
        return collect(json_decode($value))->map(fn($e) => "$year-$e")->values();
    }

    public function setDatesAttribute(array $dates)
    {
        $dates = collect($dates)
            ->sort()
            ->map(
                fn($d) => str($d)->after('-')->value()
            )
            ->values();
        $this->attributes['dates'] = json_encode($dates);
    }
}
