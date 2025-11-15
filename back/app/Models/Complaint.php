<?php

namespace App\Models;

use App\Enums\Program;
use App\Observers\UserIdObserver;
use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(UserIdObserver::class)]
class Complaint extends Model
{
    use HasComments;

    protected $fillable = [
        'client_id', 'teacher_id', 'program', 'text', 'year',
        'is_resolved',
    ];

    protected $casts = [
        'program' => Program::class,
        'is_resolved' => 'boolean',
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
