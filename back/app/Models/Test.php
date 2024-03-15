<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Test extends Model
{
    protected $fillable = [
        'name', 'minutes', 'program', 'questions'
    ];

    protected $casts = [
        'program' => Program::class,
        'questions' => 'array',
        'results' => 'array',
    ];

    public $interfaces = [
        'questions' => ['type' => 'TestQuestions'],
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


    /**
     * + 1 minute – 1 минута запас на всякий случай
     */
    public function scopeActive($query, int $clientId)
    {
        $startedAt = "results->>'$.\"{$clientId}\".started_at'";
        $finishedAt = "results->>'$.\"{$clientId}\".finished_at'";
        $now = now()->format("Y-m-d H:i:s");
        return $query->whereRaw(<<<SQL
            $startedAt is not null
            and $finishedAt is null
            and '$now' < $startedAt + interval `minutes` + 1 minute
        SQL);
    }

    public function scopeFinished($query, int $clientId)
    {
        $startedAt = "results->>'$.\"{$clientId}\".started_at'";
        $finishedAt = "results->>'$.\"{$clientId}\".finished_at'";
        $now = now()->format("Y-m-d H:i:s");
        return $query->whereRaw(<<<SQL
            $startedAt is not null
            and (
                $finishedAt is not null
                or '$now' >= $startedAt + interval `minutes` + 1 minute
            )
        SQL);
    }
}
