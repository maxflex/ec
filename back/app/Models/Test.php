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

    public function getFileAttribute($file): string
    {
        return cdn('tests', $file);
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
