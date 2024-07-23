<?php

namespace App\Models;

use App\Enums\ContractLessonStatus;
use App\Observers\ContractLessonObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ContractLessonObserver::class)]
class ContractLesson extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'contract_id', 'price', 'status',
        'minutes_late', 'is_remote', 'scores'
    ];

    protected $casts = [
        'status' => ContractLessonStatus::class,
        'is_remote' => 'boolean',
        'scores' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getScoresAttribute($value)
    {
        return json_decode($value) ?? [];
    }
}
