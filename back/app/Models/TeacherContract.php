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

    public function getHasProblemsAttribute(): bool
    {
        // Если договор не активен, считаем, что проблем нет
        if (! $this->is_active) {
            return false;
        }

        // 1. Загружаем актуальные (свежие) данные
        $actualData = self::loadData($this->teacher, $this->year);

        // 2. Берем сохраненные (старые) данные
        $savedData = $this->data ?? [];

        // 3. Функция-хелпер: превращает массив объектов в массив строк-отпечатков
        $makeSignature = function ($items) {
            return collect($items)
                ->map(function ($item) {
                    $obj = (object) $item;

                    // Создаем строку вида "1952-2500-62"
                    // %d гарантирует, что числа сравниваются как числа
                    return sprintf('%d-%d-%d', $obj->group_id, $obj->price, $obj->lessons);
                })
                ->sort()   // Сортируем (чтобы порядок записей в БД не влиял)
                ->values() // Сбрасываем ключи (важно для сравнения массивов)
                ->all();
        };

        // 4. Сравниваем два массива строк
        // Если отличается хоть что-то (длина массива, цена, кол-во), будет true
        return $makeSignature($savedData) !== $makeSignature($actualData);
    }

    public static function loadData(Teacher $teacher, int $year)
    {
        return Lesson::query()
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->excludeCancelled()
            ->where('teacher_id', $teacher->id)
            ->where('g.year', $year)
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
