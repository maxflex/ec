<?php

namespace App\Observers;

use App\Models\Instruction;

class InstructionObserver
{
    /**
     * Handle the Instruction "created" event.
     */
    public function creating(Instruction $instruction): void
    {
        if (!$instruction->entry_id) {
            $entryId = Instruction::max('entry_id') ?? 0;
            $entryId++;
            $instruction->entry_id = $entryId;
        }
    }

    /**
     * Handle the Instruction "updated" event.
     */
    public function updated(Instruction $instruction): void
    {
        //
    }

    /**
     * Handle the Instruction "deleted" event.
     */
    public function deleted(Instruction $instruction): void
    {
        //
    }

    /**
     * Handle the Instruction "restored" event.
     */
    public function restored(Instruction $instruction): void
    {
        //
    }

    /**
     * Handle the Instruction "force deleted" event.
     */
    public function forceDeleted(Instruction $instruction): void
    {
        //
    }
}
