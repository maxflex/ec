<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientGroup extends Model
{
    public $timestamps = false;

    protected $fillable = ['contract_version_program_id', 'group_id'];

    public function contractVersionProgram(): BelongsTo
    {
        return $this->belongsTo(ContractVersionProgram::class, 'contract_version_program_id', 'id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function clientLessons(): BelongsTo
    {
        return $this->belongsTo(ClientLesson::class, 'contract_version_program_id', 'id');
    }

}
