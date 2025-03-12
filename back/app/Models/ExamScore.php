<?php

namespace App\Models;

use App\Enums\Exam;
use Illuminate\Database\Eloquent\Model;

class ExamScore extends Model
{
    protected $fillable = [
        'exam', 'year', 'score', 'client_id',
        'is_published',
    ];

    protected $casts = [
        'exam' => Exam::class,
        'is_published' => 'boolean',
    ];

    public static function booted()
    {
        static::saving(function (ExamScore $examScore) {
            if ($examScore->score) {
                $examScore->max_score = $examScore->score <= 5 ? 5 : 100;
            } else {
                $examScore->max_score = null;
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function webReviews()
    {
        return $this->belongsToMany(WebReview::class);
    }
}
