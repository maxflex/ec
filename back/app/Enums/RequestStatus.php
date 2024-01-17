<?php

namespace App\Enums;

enum RequestStatus
{
    case new;
    case awaiting;
    case finished;
}
