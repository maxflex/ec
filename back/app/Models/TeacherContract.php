<?php

namespace App\Models;

use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[ObservedBy(UserIdObserver::class)]
class TeacherContract extends Model
{
    protected $fillable = [
        'year', 'date', 'data', 'file',
        'is_active', 'date_from', 'date_to',
    ];

    protected $casts = [
        'date' => 'date',
        'date_from' => 'date',
        'date_to' => 'date',
        'data' => 'array',
        'is_active' => 'boolean',
        'file' => 'array',
    ];

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
        return $this
            ->hasMany(TeacherContract::class, 'teacher_id', 'teacher_id')
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
     * @return array{groups: int, lessons: int, price: int}|null
     */
    public function getTotalAttribute(): ?array
    {
        if (! $this->data) {
            return null;
        }

        $total = [
            'groups' => collect($this->data)->pluck('group_id')->unique()->count(),
            'lessons' => 0,
            'price' => 0,
        ];

        foreach ($this->data as $d) {
            $total['price'] += ($d['price'] * $d['lessons']);
            $total['lessons'] += $d['lessons'];
        }

        return $total;
    }

    /**
     * Возвращает количество несоответствий (строк).
     * 0 — если всё совпадает.
     */
    public function getProblemsCountAttribute(): int
    {
        if (! $this->is_active) {
            return 0;
        }

        $actualData = self::loadData($this->teacher, $this->year, $this->date_from, $this->date_to);
        $savedData = $this->data ?? [];

        // Хелпер тот же: создаем массив строк-сигнатур
        $makeSignature = function ($items) {
            return collect($items)
                ->map(function ($item) {
                    $obj = (object) $item;

                    return sprintf('%d-%d-%d', $obj->group_id, $obj->price, $obj->lessons);
                })
                // Здесь сортировка уже не критична для array_diff, но полезна для отладки
                ->values()
                ->all();
        };

        $savedSigs = $makeSignature($savedData);
        $actualSigs = $makeSignature($actualData);

        // 1. Находим записи, которые "пропали" (есть в сохраненных, нет в актуальных)
        $missing = array_diff($savedSigs, $actualSigs);

        // 2. Находим записи, которые "появились" (есть в актуальных, нет в сохраненных)
        $extra = array_diff($actualSigs, $savedSigs);

        // Возвращаем сумму расхождений
        return count($missing) + count($extra);
    }

    public static function loadData(Teacher $teacher, int $year, ?string $dateFrom, ?string $dateTo): Collection
    {
        return Lesson::query()
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->excludeCancelled()
            ->where('teacher_id', $teacher->id)
            ->where('g.year', $year)
            ->when($dateFrom, fn ($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('date', '<=', $dateTo))
            ->selectRaw('group_id, price, count(*) as `lessons`')
            ->groupByRaw('group_id, price')
            ->orderByRaw('group_id, price, `lessons`')
            ->get();
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
