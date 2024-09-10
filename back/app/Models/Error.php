<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class Error extends Model
{
    const DISABLE_LOGS = true;
    public $timestamps = false;

    protected $fillable = [
        'entity_id', 'entity_type', 'code'
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public static function check()
    {
        DB::table('errors')->truncate();
        self::checkPhones();
    }

    private static function checkPhones()
    {
        $ids = Phone::query()
            ->whereRaw('length(number) <> 11')
            ->pluck('id');

        self::createErrors($ids, Phone::class);
    }

    private static function createErrors(Collection $ids, string $entityType): void
    {
        $ids->each(fn($id) => Error::create([
            'code' => 1000,
            'entity_id' => $id,
            'entity_type' => $entityType
        ]));
    }

}
