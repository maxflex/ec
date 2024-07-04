<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;

class Vacation extends Model
{
    public $timestamps = false;
    protected $fillable = ['date'];


    public function scopeWhereYear($query, $year): Builder
    {
        return $query->whereRaw(<<<SQL
            `date` between ? and ?
        SQL, [
            $year . '-09-01',
            ($year + 1) . '-05-31'
        ]);
    }
}
