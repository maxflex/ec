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

    // уведомления перед первым занятием
    case firstLessonDayAfterTomorrow = 'firstLessonDayAfterTomorrow';    // за 2 дня
    case firstLessonTomorrow = 'firstLessonTomorrow';    // за 1 день
    case firstLessonToday = 'firstLessonToday';  // за 20 минут

    public static function tryFromId($templateId): ?self
    {
        $templateId = (int) $templateId;

        foreach (self::cases() as $index => $case) {
            if ($templateId === $index) {
                return $case;
            }
        }

        return null;
    }

    public function getText(array $viewVariables = [])
    {
        $viewName = str($this->value)->snake('-')->value();

        return view('templates.'.$viewName, $viewVariables);
    }

    public function getReplyMarkup(array $callbackData = [])
    {
        $callbackData['tid'] = $this->getId();

        return match ($this) {
            self::reportPublished => new InlineKeyboardMarkup([
                [[
                    'text' => 'Посмотреть отчёт',
                    'callback_data' => json_encode($callbackData),
                ]],
            ]),

            default => null
        };
    }

    public function getId(): int
    {
        foreach (self::cases() as $index => $case) {
            if ($this === $case) {
                return $index;
            }
        }

        return -1;
    }

    public function callback($callbackData, ?int $telegramId = null)
    {
        switch ($this) {
            case self::reportPublished:
                $report = Report::find($callbackData->id);
                $report->read($telegramId);
                break;
        }
    }
}
