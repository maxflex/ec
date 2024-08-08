<?php

namespace App\Models;

use App\Enums\Exam;
use Illuminate\Database\Eloquent\Model;

class ExamScore extends Model
{
    protected $fillable = [
        'exam', 'year', 'score', 'client_id', 'web_review_id'
    ];

    protected $casts = [
        'exam' => Exam::class
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function webReview()
    {
        return $this->belongsTo(WebReview::class);
    }
}
