<?php

namespace App\Models;

use App\Traits\HasPhones;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasPhones;

    public $interfaces = [
        'groups' => ['type' => 'Groups'],
        'swamps' => ['type' => 'ContractPrograms'],
        'tests' => ['type' => 'Tests'],
    ];

    protected $hidden = ['groups', 'swamps'];

    public function contracts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Contract::class)->orderBy('id', 'desc');
    }

    public function contractGroup()
    {
        return $this->hasManyThrough(ContractGroup::class, Contract::class);
    }

    public function payments()
    {
        return $this->morphMany(ClientPayment::class, 'entity');
    }

    /**
     * @return Group[]
     */
    public function groups(): Attribute
    {
        return Attribute::make(
            fn (): Collection =>
            $this->contractGroup()->with('group')->get()->map(fn ($e) => $e->group)
        );
    }

    /**
     * @return Test[]
     */
    public function tests(): Attribute
    {
        return Attribute::make(
            fn () => Test::query()
                ->whereNotNull('results')
                ->whereRaw(<<<SQL
                    json_extract(results, '$."{$this->id}"') is not null
                SQL)
                ->latest()
                ->get()
        );
    }

    /**
     * @return ContractProgram[]
     */
    public function swamps(): Attribute
    {
        $programs = $this->groups->pluck('program');
        $result = [];

        foreach ($this->contracts as $contract) {
            foreach ($contract->versions[0]->programs as $p) {
                if (!$programs->contains($p->program)) {
                    $result[] = $p;
                }
            }
        }

        return Attribute::make(fn () => $result);
    }
}
