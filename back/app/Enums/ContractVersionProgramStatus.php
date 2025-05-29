<?php

namespace App\Enums;

enum ContractVersionProgramStatus: string
{
    case toFulfil = 'toFulfil';
    case exceedNoGroup = 'exceedNoGroup';
    case finishedNoGroup = 'finishedNoGroup';
    case inProcess = 'inProcess';
    case exceedInGroup = 'exceedInGroup';
    case finishedInGroup = 'finishedInGroup';

    /**
     * @return array<int, self>
     */
    public static function getActiveStatuses(): array
    {
        return [
            ContractVersionProgramStatus::toFulfil,
            ContractVersionProgramStatus::inProcess,
            ContractVersionProgramStatus::finishedInGroup,
        ];
    }
}
