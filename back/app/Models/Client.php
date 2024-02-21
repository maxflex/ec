<?php

namespace App\Models;

use App\Traits\HasPhones;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasPhones;

    public function contracts()
    {
        return $this->hasMany(Contract::class)->orderBy('id', 'desc');
    }

    public function contractGroup()
    {
        return $this->hasManyThrough(
            ContractGroup::class,
            Contract::class,
        );
    }

    public function groups(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->contractGroup()->with('group')->get()->map(fn ($e) => $e->group)->all()
        );
    }
}
