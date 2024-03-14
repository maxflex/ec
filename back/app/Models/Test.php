<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Test extends Model
{
    protected $fillable = [
        'name', 'minutes', 'program', 'answers'
    ];

    protected $casts = [
        'program' => Program::class,
        'answers' => 'array',
        'results' => 'array',
    ];

    public $interfaces = [
        'answers' => ['type' => 'TestAnswers'],
    ];

    public function getFileAttribute($value)
    {
        if (!$value) {
            return null;
        }
        return asset("storage/tests/" . $value);
    }

    public function scopeWhereClient($query, int $clientId)
    {
        $query
            ->whereNotNull('results')
            ->whereRaw(<<<SQL
                json_extract(results, '$."{$clientId}"') is not null
            SQL);
    }

    public static function getActive(int $clientId): Test | null
    {
        $test = Test::query()
            ->whereRaw(<<<SQL
                json_extract(results, '$."{$clientId}".started_at') is not null
            SQL)
            ->first();
        return $test;
    }
}
