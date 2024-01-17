<?php

namespace App\Enums;

enum RequestStatus
{
    case new;
    case pending;
    case finished;
}
