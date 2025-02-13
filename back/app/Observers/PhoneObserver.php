<?php

namespace App\Observers;

use App\Models\Phone;
use App\Utils\Phone as UtilsPhone;

class PhoneObserver
{
    // TODO: отправить телеграм
    // public function updated(Phone $phone): void
    // {
    //     if ($phone->isDirty('telegram_id')) {
    //     }
    // }

    public function saving(Phone $phone): void
    {
        $phone->number = UtilsPhone::clean($phone->number);
    }
}
