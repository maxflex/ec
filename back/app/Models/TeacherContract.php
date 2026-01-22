<?php

namespace App\Models;

use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(UserIdObserver::class)]
class TeacherContract extends Model
{
    protected $fillable = [
        'year', 'date', 'data', 'file',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'data' => 'array',
        'is_active' => 'boolean',
        'file' => 'array',
    ];

    public static function loadData(Teacher $teacher, int $year)
    {
        return Lesson::query()
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->excludeCancelled()
            ->where('teacher_id', $teacher->id)
            ->where('g.year', $year)
            ->selectRaw('group_id, price, count(*) as cnt')
            ->groupByRaw('group_id, price')
            ->orderByRaw('group_id, price, cnt')
            ->get();
    }

    public static function booted()
    {
        static::creating(function (TeacherContract $teacherContract) {
            $teacherContract->chain()->update([
                'is_active' => false,
            ]);
            $teacherContract->is_active = true;
        });
    }

    /**
     * @return HasMany<TeacherContract>
     */
    public function chain(): HasMany
    {
        return $this->hasMany(TeacherContract::class, 'teacher_id', 'teacher_id')
            ->where('year', $this->year);
    }

    /**
     * Номер по порядку
     */
    public function getSeqAttribute(): int
    {
        return $this->chain()
            ->where('created_at', '<=', $this->created_at)
            ->count();
    }

    /**
     * @return BelongsTo<Teacher>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
