<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class WebReview extends Model
{
    protected $fillable = [
        'text', 'signature', 'rating', 'client_id',
        'programs',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function examScores(): BelongsToMany
    {
        return $this->belongsToMany(ExamScore::class);
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->client->photo !== null;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopePublished($query)
    {
        $query
            ->whereNotNull('text')
            ->whereNotNull('signature')
            ->whereHas('client.photo');
    }

    /**
     * @return Program[]
     */
    public function getProgramsAttribute()
    {
        return DB::table('web_review_program')
            ->where('web_review_id', $this->id)
            ->pluck('program')
            ->map(fn ($program) => Program::from($program))
            ->all();
    }

    public function setProgramsAttribute(array $programs)
    {
        DB::table('web_review_program')->where('web_review_id', $this->id)->delete();
        DB::table('web_review_program')->insert(collect($programs)->map(fn ($program) => [
            'web_review_id' => $this->id,
            'program' => $program,
        ])->all());
    }
}
