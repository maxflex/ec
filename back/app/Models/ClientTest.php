<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ClientTest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name', 'minutes', 'program', 'questions', 'file',
    ];

    protected $casts = [
        'program' => Program::class,
        'questions' => 'array',
        'answers' => 'array',
    ];

    /**
     * + 1 minute – 1 минута запас на всякий случай
     */
    public function scopeActive($query)
    {
        return $query->whereRaw(<<<SQL
            started_at is not null
            and finished_at is null
            and now() < started_at + interval `minutes` + 1 minute
        SQL);
    }

    public function isFinished(): Attribute
    {
        return Attribute::make(
            fn (): bool => $this->started_at !== null && (
                $this->finished_at !== null
                || $this->started_at < now()
                ->subMinutes($this->minutes)
                ->subMinute()
                ->format('Y-m-d H:i:s')
            )
        );
    }

    public function questionsCount(): Attribute
    {
        return Attribute::make(
            fn (): int => count($this->questions)
        );
    }

    public function getFileAttribute($file): string
    {
        return cdn('tests', $file);
    }
}
