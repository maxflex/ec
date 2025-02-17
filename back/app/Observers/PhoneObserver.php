<?php

namespace App\Observers;

use App\Models\Phone;
use App\Utils\Phone as UtilsPhone;

class PhoneObserver
{
    public function saving(Phone $phone): void
    {
        $phone->number = UtilsPhone::clean($phone->number);
    }
}
