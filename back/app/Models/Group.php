<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    public $interfaces = [
        'zoom' => ['type' => 'Zoom']
    ];

    protected $casts = [
        'program' => Program::class,
        'zoom' => 'array'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function contracts()
    {
        return $this->belongsToMany(Contract::class);
    }
}
