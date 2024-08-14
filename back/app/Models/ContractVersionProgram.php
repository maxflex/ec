<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;

class ContractVersionProgram extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'program', 'prices', 'lessons_planned', 'is_closed'
    ];

    protected $casts = [
        'program' => Program::class,
        'is_closed' => 'boolean',
        'prices' => 'array',
    ];

    protected $attributes = [
        'prices' => []
    ];

    public function scopeActive($query)
    {
        $query->where('is_closed', false);
    }

    public function scopeClosed($query)
    {
        $query->where('is_closed', true);
    }

    public function contractVersion()
    {
        return $this->belongsTo(ContractVersion::class);
    }

    public function getNextPrice(): int
    {
//        $nextLessonIndex = ClientLesson::query()
//            ->where('contract_id', $this->contractVersion->contract_id)
//            ->count();
//        $nextLessonIndex++;
        $prices = $this->prices;
        return intval($prices[count($prices) - 1][1]); // последняя цена
    }
}
