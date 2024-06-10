<?php

namespace App\Models;

use App\Enums\CompanyType;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'year', 'company', 'client_id'
    ];

    protected $casts = [
        'company' => CompanyType::class,
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function versions()
    {
        return $this->hasMany(ContractVersion::class)->orderBy('version', 'desc');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function payments()
    {
        return $this->morphMany(ClientPayment::class, 'entity');
    }
}
