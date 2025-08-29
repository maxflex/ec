<?php

namespace App\Enums;

enum RouteGroup
{
    case admin;
    case client;
    case representative;
    case teacher;
    case pub;
}
