<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientReview extends Model
{
    protected $fillable = [
        'client_id', 'teacher_id', 'program', 'rating', 'text',
    ];

    protected $casts = [
        'program' => Program::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
