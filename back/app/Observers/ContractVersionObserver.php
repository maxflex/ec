<?php

namespace App\Observers;

use App\Models\ContractVersion;
use Illuminate\Support\Facades\Schema;

class ContractVersionObserver
{
    public function deleting(ContractVersion $contractVersion): void
    {
        $prev = $contractVersion->chain()->where('is_active', false)->latest()->first();
        // удаляем последнюю версию + есть другие версии, то откатываемся к другой
        if ($contractVersion->is_active && $prev !== null) {
            foreach ($contractVersion->programs as $program) {
                $prevProgram = $prev->programs()->where('program', $program->program)->first();
                if ($prevProgram === null) {
                    $program->delete();
                    continue;
                }
                // TODO: лучше обновлять зависимые модели, чем ID
                Schema::withoutForeignKeyConstraints(function () use ($program, $prevProgram) {
                    $program->delete();
                    $prevProgram->prices()->update([
                        'contract_version_program_id' => $program->id
                    ]);
                    $prevProgram->id = $program->id;
                    $prevProgram->save();
                });
            }
            $prev->is_active = true;
            $prev->save();
        } else {
            $contractVersion->programs->each->delete();
        }
        $contractVersion->payments->each->delete();
    }

    public function deleted(ContractVersion $contractVersion): void
    {
        // если удалили последнюю версию, то сносим весь договор
        if ($contractVersion->chain()->count() === 0) {
            $contractVersion->contract->delete();
        }
    }
}
