<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebReview extends Model
{
    protected $fillable = [
        'text', 'signature', 'rating', 'is_published', 'client_id'
    ];

    protected $casts = [
        'is_published' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function examScore()
    {
        return $this->hasOne(ExamScore::class);
    }
}
