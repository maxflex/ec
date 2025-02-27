<?php

namespace App\Observers;

use App\Models\Phone;
use App\Models\TelegramMessage;
use App\Utils\Phone as UtilsPhone;

class PhoneObserver
{
    public function saving(Phone $phone): void
    {
        $phone->number = UtilsPhone::clean($phone->number);
    }

    // public function updating(Phone $phone)
    // {
    //     if ($phone->isDirty('number') && $phone->telegram_id) {
    //         TelegramMessage::sendNumberChanged($phone);
    //         $phone->telegram_id = null;
    //     }
    // }
    //
    // public function deleting(Phone $phone)
    // {
    //     if ($phone->telegram_id) {
    //         TelegramMessage::sendNumberChanged($phone);
    //     }
    // }
}
