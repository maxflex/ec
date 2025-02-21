<?php

namespace App\Enums;

enum Exam: string
{
    case ogeMath = 'ogeMath';
    case ogePhys = 'ogePhys';
    case ogeChem = 'ogeChem';
    case ogeBio = 'ogeBio';
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
    case egeBio = 'egeBio';
    case egeEng = 'egeEng';
    case egeInf = 'egeInf';
    case egeRus = 'egeRus';
    case egeLit = 'egeLit';
    case egeGeo = 'egeGeo';
    case egeHis = 'egeHis';
    case egeSoc = 'egeSoc';

    case engSpoken = 'engSpoken';
    case izl9 = 'izl9';
    case soch11 = 'soch11';

    public function getName(): string
    {
        return match ($this) {
            self::ogeMath => 'ОГЭ математика',
            self::ogePhys => 'ОГЭ физика',
            self::ogeChem => 'ОГЭ химия',
            self::ogeBio => 'ОГЭ биология',
            self::ogeEng => 'ОГЭ английский',
            self::ogeInf => 'ОГЭ информатика',
            self::ogeRus => 'ОГЭ русский',
            self::ogeLit => 'ОГЭ литература',
            self::ogeGeo => 'ОГЭ география',
            self::ogeHis => 'ОГЭ история',
            self::ogeSoc => 'ОГЭ обществознание',
            self::egeMathBase => 'ЕГЭ математика база',
            self::egeMathProf => 'ЕГЭ математика профиль',
            self::egePhys => 'ЕГЭ физика',
            self::egeChem => 'ЕГЭ химия',
            self::egeBio => 'ЕГЭ биология',
            self::egeEng => 'ЕГЭ английский',
            self::egeInf => 'ЕГЭ информатика',
            self::egeRus => 'ЕГЭ русский',
            self::egeLit => 'ЕГЭ литература',
            self::egeGeo => 'ЕГЭ география',
            self::egeHis => 'ЕГЭ история',
            self::egeSoc => 'ЕГЭ обществознание',
            self::engSpoken => 'Разговорный английский',
            self::izl9 => 'Изложение 9 класс',
            self::soch11 => 'Сочинение 11 класс',
        };
    }
}
