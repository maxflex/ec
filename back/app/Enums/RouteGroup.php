<?php

namespace App\Enums;

enum RouteGroup
{
    case admin;
    case student;
    case representative;
    case teacher;
    case pub;
}
