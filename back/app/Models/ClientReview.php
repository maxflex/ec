<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class ClientReview extends Model
{
    protected $fillable = [
        'program', 'text', 'rating', 'score', 'max_score'
    ];

    protected $casts = [
        'program' => Program::class
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
            price,
            NULL as lessons_count
        SQL);
    }
}
