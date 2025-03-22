<?php

namespace App\Enums;

use App\Models\Report;
use App\Utils\ClientReviewMessage;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

enum TelegramTemplate: string
{
    case reportPublished = 'reportPublished';
    case reportRead = 'reportRead';
    case clientLessonStatus = 'clientLessonStatus';
    case teacherConductMissing = 'teacherConductMissing';
    case paymentReminder = 'paymentReminder';
    case unplannedOrCancelled = 'unplannedOrCancelled';
    case clientReviewRating = 'clientReviewRating';
    case clientReviewText = 'clientReviewText';

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

            self::clientReviewRating => new InlineKeyboardMarkup([
                [
                    // ['text' => '1 💔', 'callback_data' => json_encode([...$callbackData, 'rating' => 1])],
                    // ['text' => '2 👎', 'callback_data' => json_encode([...$callbackData, 'rating' => 2])],
                    // ['text' => '3 😐', 'callback_data' => json_encode([...$callbackData, 'rating' => 3])],
                    // ['text' => '4 👍', 'callback_data' => json_encode([...$callbackData, 'rating' => 4])],
                    // ['text' => '5 ⭐', 'callback_data' => json_encode([...$callbackData, 'rating' => 5])],
                    ['text' => '1 ⭐', 'callback_data' => json_encode([...$callbackData, 'rating' => 1])],
                    ['text' => '2 ⭐', 'callback_data' => json_encode([...$callbackData, 'rating' => 2])],
                    ['text' => '3 ⭐', 'callback_data' => json_encode([...$callbackData, 'rating' => 3])],
                    ['text' => '4 ⭐', 'callback_data' => json_encode([...$callbackData, 'rating' => 4])],
                    ['text' => '5 ⭐', 'callback_data' => json_encode([...$callbackData, 'rating' => 5])],
                ],
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

            case self::clientReviewRating:
                $clientReviewMessage = new ClientReviewMessage($callbackData->id);
                $clientReviewMessage->setRating((int) $callbackData->rating);
                break;
        }
    }
}
