<?php

namespace App\Enums;

enum CallState: string
{
    case appeared = 'Appeared';
    case connected = 'Connected';
    case disconnected = 'Disconnected';
    case onHold = 'OnHold'; // не обрабатывается
}
