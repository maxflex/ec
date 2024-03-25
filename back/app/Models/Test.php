<?php

namespace App\Models;

use App\Enums\Program;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = [
        'name', 'minutes', 'program', 'questions'
    ];

    protected $casts = [
        'program' => Program::class,
        'questions' => 'array',
    ];

    public $interfaces = [
        'questions' => ['type' => 'TestQuestions'],
    ];

    public function file(): Attribute
    {
        return Attribute::make(
            fn ($v): string => asset("storage/tests/" . $v)
        );
    }

    public function addClient(Client $client)
    {
        $client->tests()->create([
            'program' => $this->program,
            'name' => $this->name,
            'file' => $this->getRawOriginal('file'),
            'minutes' => $this->minutes,
            'questions' => $this->questions,
        ]);
    }
}
