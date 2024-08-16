<?php

namespace App\Models;

use App\Enums\ClientLessonStatus;
use App\Observers\ClientLessonObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ClientLessonObserver::class)]
class ClientLesson extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'contract_id', 'price', 'status',
        'minutes_late', 'is_remote', 'scores'
    ];

    protected $casts = [
        'status' => ClientLessonStatus::class,
        'is_remote' => 'boolean',
        'scores' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function contractVersionProgram()
    {
        return $this->belongsTo(ContractVersionProgram::class);
    }

    public function getScoresAttribute($value)
    {
        return json_decode($value) ?? [];
    }
}
