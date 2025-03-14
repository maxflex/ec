<?php

namespace App\Enums;

enum RequestStatus: string
{
    case new = 'new';
    case finished = 'finished';
    case waiting = 'waiting';
    case refused = 'refused';
}
