<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = [
        'name', 'minutes', 'program', 'questions', 'file'
    ];

    protected $casts = [
        'program' => Program::class,
        'questions' => JsonArrayCast::class,
        'file' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
