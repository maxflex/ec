<?php

namespace App\Utils;

use DB;
use Illuminate\Database\Query\Builder;

class MigrationError
{
    public static function create(
        string  $message,
        ?string $newTable = null,
        ?string $oldTable = null,
        ?int    $newId = null,
        ?int    $oldId = null,
    )
    {
        self::table()->insert([
            'new_table' => $newTable,
            'new_id' => $newId,
            'old_table' => $oldTable,
            'old_id' => $oldId,
            'message' => $message
        ]);
    }

    public static function table(): Builder
    {
        return DB::table('migration_errors');
    }
}