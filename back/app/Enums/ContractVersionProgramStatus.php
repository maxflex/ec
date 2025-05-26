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
}
