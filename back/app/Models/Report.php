<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'year', 'program', 'price', 'homework_comment',
        'is_moderated', 'is_published', 'client_id'
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

    public function scopePrepareForUnion($query)
    {
        return $query->selectRaw(<<<SQL
            id,
            teacher_id,
            client_id,
            year,
            program,
            is_moderated,
            is_published,
            created_at,
            NULL as lessons_since_last_report
        SQL);
    }
}
