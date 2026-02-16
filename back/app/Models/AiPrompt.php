<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    public const int REPORT_INSTRUCTION = 1;

    public const int REPORT = 2; // значит, используем этот

    protected $fillable = [
        'title', 'text',
    ];

    /**
     * Резолвим человекочитаемый alias в фиксированный ID prompt.
     */
    public static function resolveAlias(string $alias): int
    {
        return match ($alias) {
            'report' => self::REPORT,
            default => throw new \InvalidArgumentException("Неизвестный alias AI prompt: {$alias}"),
        };
    }
}
