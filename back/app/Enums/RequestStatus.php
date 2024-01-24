<?php

namespace App\Enums;

enum RequestStatus: string
{
    case new = 'new';
    case awaiting = 'awaiting';
    case finished = 'finished';
}
