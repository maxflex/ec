<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $casts = [
        'status' => RequestStatus::class,
    ];
}
