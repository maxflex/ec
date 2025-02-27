<?php

namespace App\Enums;

enum ReportDelivery: string
{
    case delivered = 'delivered';
    case read = 'read';
}
