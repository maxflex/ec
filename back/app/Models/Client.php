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
        'tests' => ['type' => 'ClientTests'],
        'head_teacher' => ['type' => 'Teacher | null'],
        'branches' => ['type' => 'string[]'],
    ];

    protected $hidden = ['groups', 'swamps'];

    public function tests()
    {
        return $this->hasMany(ClientTest::class);
    }

    public function contracts()
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

    public function getBranchesAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function headTeacher()
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }
}
