<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function examScores(): BelongsToMany
    {
        return $this->belongsToMany(ExamScore::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
