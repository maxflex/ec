<?php

namespace App\Models;

use App\Enums\{Program, RequestStatus};
use App\Traits\HasPhones;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasPhones;

    protected $casts = [
        'status' => RequestStatus::class,
        'program' => Program::class,
    ];

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
