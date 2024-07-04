<?php

namespace App\Models;

use App\Enums\ContractLessonStatus;
use Illuminate\Database\Eloquent\Model;

class ContractLesson extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'contract_id', 'price', 'status',
        'minutes_late', 'is_remote'
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

    public function getScoresAttribute($value)
    {
        return $value ?? [];
    }
}
