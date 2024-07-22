<?php

namespace App\Enums;

use App\Models\Report;
use App\Models\TelegramMessage;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;


enum TelegramTemplate: string
{
    case reportPublished = 'reportPublished';
    case reportRead = 'reportRead';

    public function getText(array $data = [])
    {
        switch ($this) {
            case self::reportPublished:
            case self::reportRead:
                $variables = [
                    'report' => Report::find($data['id']),
                ];
                break;
            default:
                $variables = [];
        }
        // reportPublished => report-published
        $viewName = str($this->value)->snake("-")->value();
        return view('templates.' .  $viewName, $variables);
    }

    public function getReplyMarkup(array $data = [])
    {
        $callbackData = json_encode([
            ...$data,
            'template' => $this->value,
        ]);
        return match ($this) {
            self::reportPublished => new InlineKeyboardMarkup([
                [[
                    'text' => 'Посмотреть отчёт',
                    'callback_data' => $callbackData
                ]]
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
        };
    }
}
