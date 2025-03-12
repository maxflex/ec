<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
    public function getExamScoresAttribute(): Collection
    {
        $examScores = collect();
        foreach ($this->programs as $program) {
            $exam = $program->getExam();
            if ($exam === null) {
                continue;
            }
            $examScore = ExamScore::query()
                ->where('exam', $exam)
                ->where('is_published', true)
                ->where('client_id', $this->client_id)
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
