<?php

namespace App\Models;

use App\Http\Resources\WebReviewResource;
use Illuminate\Database\Eloquent\Model;

class WebReview extends Model
{
    protected $fillable = [
        'text', 'signature', 'rating', 'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function scores()
    {
        return $this->hasMany(WebReviewScore::class);
    }
}
