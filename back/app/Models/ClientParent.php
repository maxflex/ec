<?php

namespace App\Models;

use App\Traits\HasPhones;
use App\Traits\IsPerson;
use App\Traits\RelationSyncable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class ClientParent extends Model
{
    use IsPerson, HasPhones, RelationSyncable, Searchable;

    public $timestamps = false;

    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'passport_series', 'passport_number',
        'passport_address', 'passport_code', 'passport_issued_date', 'passport_issued_by',
        'fact_address'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function searchableAs()
    {
        return 'people';
    }

    public function toSearchableArray()
    {
        $class = class_basename(self::class);

        return [
            'id' => implode('-', [$class, $this->id]),
            'first_name' => $this->first_name ?? '',
            'last_name' => $this->last_name ?? '',
            'middle_name' => $this->middle_name ?? '',
            'type' => strtolower($class),
            'phones' => $this->phones()->pluck('number'),
        ];
    }
}
