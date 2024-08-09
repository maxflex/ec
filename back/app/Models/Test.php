<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = [
        'name', 'minutes', 'program', 'questions'
    ];

    protected $casts = [
        'program' => Program::class,
        'questions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFileAttribute($file): ?string
    {
        if (!$file) {
            return null;
        }
        return cdn('tests', $file);
    }
}
