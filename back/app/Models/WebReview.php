<?php

namespace App\Models;

use App\Enums\Program;
use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[ObservedBy(UserIdObserver::class)]
class WebReview extends Model
{
    protected $fillable = [
        'text', 'signature', 'rating', 'client_id', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return Collection<int, ExamScore>
     */
    public function getExamScores(bool $onlyPublished): Collection
    {
        $examScores = collect();
        foreach ($this->programs as $program) {
            $exam = $program->getExam();
            if ($exam === null) {
                continue;
            }
            $examScore = ExamScore::query()
                ->where('exam', $exam)
                ->where('client_id', $this->client_id)
                ->when($onlyPublished, fn ($q) => $q->where('is_published', true))
                ->first();
            if ($examScore === null) {
                continue;
            }
            $examScores->push($examScore);
        }

        return $examScores->unique('id');
    }

    /**
     * @return Program[]
     */
    public function getProgramsAttribute()
    {
        return $this->webReviewPrograms->pluck('program')->all();
    }

    public function savePrograms(array $programs): void
    {
        $this->webReviewPrograms()->delete();
        $this->webReviewPrograms()->createMany(collect($programs)->map(fn ($program) => [
            'program' => $program,
        ]));
    }

    public function webReviewPrograms(): HasMany
    {
        return $this->hasMany(WebReviewProgram::class);
    }
}
