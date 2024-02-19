<?php

namespace App\Models;

use App\Enums\{Program, RequestStatus};
use App\Traits\HasPhones;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Request extends Model
{
    use HasPhones;

    protected $casts = [
        'status' => RequestStatus::class,
        'program' => Program::class,
    ];

    /**
     * @return list<Client>
     */
    public function clients(): Attribute
    {
        $numbers = $this->phones->pluck('number')->unique();
        $clients = Client::whereHas('phones', fn ($q) => $q->whereIn('number', $numbers))->get()->all();
        return Attribute::make(
            fn () => $clients
        );
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
