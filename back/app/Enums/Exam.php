<?php

namespace App\Enums;

enum Exam: string
{
    case ogeMath = 'ogeMath';
    case ogePhys = 'ogePhys';
    case ogeChem = 'ogeChem';
    case ogeEng = 'ogeEng';
    case ogeInf = 'ogeInf';
    case ogeRus = 'ogeRus';
    case ogeLit = 'ogeLit';
    case ogeGeo = 'ogeGeo';
    case ogeHis = 'ogeHis';
    case ogeSoc = 'ogeSoc';
    case egeMathBase = 'egeMathBase';
    case egeMathProf = 'egeMathProf';
    case egePhys = 'egePhys';
    case egeChem = 'egeChem';
    case egeEng = 'egeEng';
    case egeInf = 'egeInf';
    case egeRus = 'egeRus';
    case egeLit = 'egeLit';
    case egeGeo = 'egeGeo';
    case egeHis = 'egeHis';
    case egeSoc = 'egeSoc';

    /**
     * @return Program[]
     */
    public function getPrograms()
    {
        return match ($this) {
            self::ogeMath => [Program::math9, Program::mathSchool9],
            default => []
        };
    }
}
