<?php

namespace App\Models;

use App\Enums\CompanyType;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    public $interfaces = [
        'year' => ['type' => 'Year'],
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
}
