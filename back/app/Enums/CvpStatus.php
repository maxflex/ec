<?php

namespace App\Enums;

enum CvpStatus: string
{
    case active = 'active';
    case finished = 'finished';
    case exceeded = 'exceeded';

    /**
     * @return array<int, self>
     */
    public static function getActiveStatuses(): array
    {
        return [
            CvpStatus::active,
        ];
    }
}
