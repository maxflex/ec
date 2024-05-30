<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientReview extends Model
{
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
