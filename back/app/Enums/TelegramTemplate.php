<?php

namespace App\Enums;

use App\Models\Report;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

enum TelegramTemplate: string
{
    case reportPublished = 'reportPublished';
    case reportRead = 'reportRead';
    case clientLessonStatus = 'clientLessonStatus';
    case teacherConductMissing = 'teacherConductMissing';
    case paymentReminder = 'paymentReminder';
    case unplannedOrCancelled = 'unplannedOrCancelled';

    public function getText(array $viewVariables = [])
    {
        $viewName = str($this->value)->snake('-')->value();

        return view('templates.'.$viewName, $viewVariables);
    }

    public function getReplyMarkup(array $callbackData = [])
    {
        $callbackData = json_encode([
            ...$callbackData,
            'template' => $this->value,
        ]);

        return match ($this) {
            self::reportPublished => new InlineKeyboardMarkup([
                [[
                    'text' => 'Посмотреть отчёт',
                    'callback_data' => $callbackData,
                ]],
            ]),
            default => null
        };
    }

    public function callback($callbackData)
    {
        switch ($this) {
            case self::reportPublished:
                $report = Report::find($callbackData->id);
                $report->read();
                break;
        }
    }
}
