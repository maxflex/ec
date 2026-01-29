<?php

namespace App\Models;

use App\Http\Resources\PersonResource;
use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

#[ObservedBy(UserIdObserver::class)]
class TeacherAct extends Model
{
    protected $fillable = [
        'year', 'date', 'data', 'date_from', 'date_to',
    ];

    protected $casts = [
        'date' => 'date',
        'date_from' => 'date',
        'date_to' => 'date',
        'data' => 'array',
    ];

    /**
     * Загрузить данные для массового добавления актов
     */
    public static function loadMassData(int $year, ?string $dateFrom, ?string $dateTo)
    {
        $data = Lesson::query()
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->excludeCancelled()
            ->with('teacher')
            ->where('g.year', $year)
            ->when($dateFrom, fn ($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('date', '<=', $dateTo))
            ->selectRaw('teacher_id, group_id, price, count(*) as `lessons`')
            ->groupByRaw('teacher_id, group_id, price')
            ->orderByRaw('teacher_id, group_id, price, `lessons`')
            ->get()
            ->groupBy('teacher_id');

        $result = collect();
        foreach ($data as $items) {
            $result->push([
                'teacher' => new PersonResource($items[0]->teacher),
                'groups' => $items->pluck('group_id')->unique()->count(),
                'lessons' => $items->sum('lessons'),
                'price' => $items->sum(fn ($e) => intval($e->price) * intval($e->lessons)),
            ]);
        }

        return $result
            ->sortBy(['teacher.last_name', 'teacher.first_name', 'teacher.middle_name'])
            ->values()
            ->all();
    }

    public static function getVariables(Request $request): Collection
    {
        $teachers = Teacher::whereIn('id', $request->teacher_ids)->get();

        $result = collect();
        foreach ($teachers as $teacher) {
            $result->push((object) [
                ...$request->except('teacher_ids'),
                'teacher' => $teacher,
                'data' => TeacherContract::loadData($teacher, $request->year, $request->date_from, $request->date_to),
            ]);
        }

        return $result;
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
     * @return HasMany<TeacherAct>
     */
    public function chain(): HasMany
    {
        return $this
            ->hasMany(TeacherAct::class, 'teacher_id', 'teacher_id')
            ->where('year', $this->year);
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
