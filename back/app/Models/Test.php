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
}
