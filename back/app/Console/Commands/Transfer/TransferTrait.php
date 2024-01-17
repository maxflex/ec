<?php

namespace App\Console\Commands\Transfer;

use App\Models\User;
use Illuminate\Support\Facades\DB;

trait TransferTrait
{
    protected function getUserId($createdEmailId): int | null
    {
        if (!$createdEmailId) {
            return null;
        }
        $adminId = DB::connection('egecrm')->table('emails')->whereId($createdEmailId)->value('entity_id');
        return User::find($adminId)->id;
    }

    protected function nullify(string | null $text): string | null
    {
        if ($text === null) {
            return null;
        }
        $text = trim($text);
        return $text ? $text : null;
    }

    protected function mapEnum(string $commaSeparated, string $enumClass): string | null
    {
        if ($commaSeparated === "") {
            return null;
        }
        return collect(explode(',', $commaSeparated))
            ->map(fn ($id) => $enumClass::getById(intval($id))->name)
            ->join(',');
    }
}
