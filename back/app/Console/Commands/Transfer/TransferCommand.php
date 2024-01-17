<?php

namespace App\Console\Commands\Transfer;

trait TransferCommand
{
    protected function nullify(string $text): string | null
    {
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
