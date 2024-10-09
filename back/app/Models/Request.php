<?php

namespace App\Models;

use App\Enums\{Program, RequestStatus};
use App\Traits\{HasComments, HasPhones, RelationSyncable};
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasPhones, HasComments, RelationSyncable;

    protected $casts = [
        'status' => RequestStatus::class,
        'program' => Program::class,
    ];

    protected $fillable = [
        'responsible_user_id', 'program', 'comment', 'status', 'client_id'
    ];

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function booted()
    {
        self::creating(function ($request) {
            $request->ip = request()->ip();
        });
    }
}
