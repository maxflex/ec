<?php

namespace App\Enums;

enum EventParticipantConfirmation: string
{
    case pending = 'pending';
    case confirmed = 'confirmed';
    case rejected = 'rejected';
}
