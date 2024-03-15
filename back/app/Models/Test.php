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

    public function start(int $clientId)
    {
        $results = $this->results;
        $results[$clientId] = [
            'started_at' => now()->format('Y-m-d H:i:s')
        ];
        $this->results = $results;
        $this->save();
    }

    public function finish(int $clientId, array $answers)
    {
        $results = $this->results;
        $results[$clientId] = [
            ...$results[$clientId],
            'finished_at' => now()->format('Y-m-d H:i:s'),
            'answers' => $answers,
            'test' => extract_fields($this, [
                'program', 'name', 'file', 'minutes', 'answers'
            ])
        ];
        $this->results = $results;
        $this->save();
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
