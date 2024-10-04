<?php

namespace App\Enums;

enum TelegramListStatus: string
{
    case scheduled = 'scheduled';
    case sending = 'sending';
    case sent = 'sent';
}
