<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'year', 'program', 'price', 'homework_comment',
        'is_moderated', 'is_published'
    ];

    protected $casts = [
        'is_moderated' => 'boolean',
        'is_published' => 'boolean',
        'program' => Program::class,
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
