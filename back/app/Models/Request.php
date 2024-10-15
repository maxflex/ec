<?php

namespace App\Models;

use App\Enums\{Program, RequestStatus};
use App\Traits\{HasComments, HasPhones, RelationSyncable};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passes(): HasMany
    {
        return $this->hasMany(Pass::class);
    }

    public static function booted()
    {
        self::creating(function ($request) {
            $request->ip = request()->ip();
        });
    }
}
