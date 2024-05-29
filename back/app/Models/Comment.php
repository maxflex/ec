<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'text', 'entity_id', 'entity_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
