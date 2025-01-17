<?php

namespace App\Models;

use App\Casts\JsonArrayCast;
use App\Enums\ClientLessonStatus;
use App\Observers\ClientLessonObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ClientLessonObserver::class)]
class ClientLesson extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'price', 'status', 'minutes_late', 'scores',
        'contract_version_program_id', 'comment'
    ];

    protected $casts = [
        'status' => ClientLessonStatus::class,
        'scores' => JsonArrayCast::class,
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function contractVersionProgram(): BelongsTo
    {
        return $this->belongsTo(ContractVersionProgram::class);
    }
}
