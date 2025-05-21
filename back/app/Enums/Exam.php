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
            self::ogeMath => 'ОГЭ по математике',
            self::ogePhys => 'ОГЭ по физике',
            self::ogeChem => 'ОГЭ по химии',
            self::ogeBio => 'ОГЭ по биологии',
            self::ogeEng => 'ОГЭ по английскому языку',
            self::ogeInf => 'ОГЭ по информатике',
            self::ogeRus => 'ОГЭ по русскому языку',
            self::ogeLit => 'ОГЭ по литературе',
            self::ogeGeo => 'ОГЭ по географии',
            self::ogeHis => 'ОГЭ по истории',
            self::ogeSoc => 'ОГЭ по обществознанию',
            self::egeMathBase => 'ЕГЭ по математике (база)',
            self::egeMathProf => 'ЕГЭ по математике (профиль)',
            self::egePhys => 'ЕГЭ по физике',
            self::egeChem => 'ЕГЭ по химии',
            self::egeBio => 'ЕГЭ по биологии',
            self::egeEng => 'ЕГЭ по английскому языку',
            self::egeInf => 'ЕГЭ по информатике',
            self::egeRus => 'ЕГЭ по русскому языку',
            self::egeLit => 'ЕГЭ по литературе',
            self::egeGeo => 'ЕГЭ по географии',
            self::egeHis => 'ЕГЭ по истории',
            self::egeSoc => 'ЕГЭ по обществознанию',
            self::engSpoken => 'Разговорный английский',
            self::izl9 => 'Изложение 9 класс',
            self::soch11 => 'Сочинение 11 класс',
        };
    }

    /**
     * @return array<int, Program>
     */
    public function getPrograms(): array
    {
        $result = [];
        foreach (Program::cases() as $program) {
            if ($program->getExam() === $this) {
                $result[] = $program;
            }
        }

        return $result;
    }
}
