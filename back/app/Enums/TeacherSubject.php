<?php

namespace App\Enums;

enum TeacherSubject: string
{
    case math = 'math';
    case phys = 'phys';
    case chem = 'chem';
    case bio = 'bio';
    case inf = 'inf';
    case rus = 'rus';
    case lit = 'lit';
    case soc = 'soc';
    case his = 'his';
    case eng = 'eng';
    case geo = 'geo';
    case soch = 'soch';

    /**
     * ID предмета (для переноса из старой БД)
     */
    public static function getById(int $id): self
    {
        return match ($id) {
            1 => self::math,
            2 => self::phys,
            3 => self::chem,
            4 => self::bio,
            5 => self::inf,
            6 => self::rus,
            7 => self::lit,
            8 => self::soc,
            9 => self::his,
            10 => self::eng,
            11 => self::geo,
            12 => self::soch,
            default => self::math
        };
    }

    public function text(): string
    {
        return match ($this) {
            self::math => 'математика',
            self::phys => 'физика',
            self::chem => 'химия',
            self::bio => 'биология',
            self::inf => 'информатика',
            self::rus => 'русский язык',
            self::lit => 'литература',
            self::soc => 'обществознание',
            self::his => 'история',
            self::eng => 'английский язык',
            self::geo => 'география',
            self::soch => 'сочинение',
        };
    }

    public function dative(): string
    {
        return match ($this) {
            self::math => 'математике',
            self::phys => 'физике',
            self::chem => 'химии',
            self::bio => 'биологии',
            self::inf => 'информатике',
            self::rus => 'русскому языку',
            self::lit => 'литературе',
            self::soc => 'обществознанию',
            self::his => 'истории',
            self::eng => 'английскому языку',
            self::geo => 'географии',
            self::soch => 'сочинению',
        };
    }

    public function accusative(): string
    {
        return match ($this) {
            self::math => 'математику',
            self::phys => 'физику',
            self::chem => 'химию',
            self::bio => 'биологию',
            self::inf => 'информатику',
            self::rus => 'русский язык',
            self::lit => 'литературу',
            self::soc => 'обществознание',
            self::his => 'историю',
            self::eng => 'английский язык',
            self::geo => 'географию',
            self::soch => 'сочинение',
        };
    }

    public function instrumental(): string
    {
        return match ($this) {
            self::math => 'математикой',
            self::phys => 'физикой',
            self::chem => 'химией',
            self::bio => 'биологией',
            self::inf => 'информатикой',
            self::rus => 'русским языком',
            self::lit => 'литературой',
            self::soc => 'обществознанием',
            self::his => 'историей',
            self::eng => 'английским',
            self::geo => 'географией',
            self::soch => 'сочинением',
        };
    }

    public function short(): string
    {
        return match ($this) {
            self::math => 'МАТ',
            self::phys => 'ФИЗ',
            self::chem => 'ХИМ',
            self::bio => 'БИО',
            self::inf => 'ИНФ',
            self::rus => 'РУС',
            self::lit => 'ЛИТ',
            self::soc => 'ОБЩ',
            self::his => 'ИСТ',
            self::eng => 'АНГ',
            self::geo => 'ГЕО',
            self::soch => 'СОЧ',
        };
    }

    /**
     * для генерации отзыва
     * (русский язык стал / обществознание стало) самым любимым
     */
    public function became(): string
    {
        return match ($this) {
            self::math => 'стала',
            self::phys => 'стала',
            self::chem => 'стала',
            self::bio => 'стала',
            self::inf => 'стала',
            self::rus => 'стал',
            self::lit => 'стала',
            self::soc => 'стало',
            self::his => 'стала',
            self::eng => 'стал',
            self::geo => 'стала',
            self::soch => 'стало',
        };
    }

    // public static function getByTranslit($text)
    // {
    //     return match ($text) {
    //         'matematike' => self::math,
    //         'fizike' => self::phys,
    //         'himii' => self::chem,
    //         'biologii' => self::bio,
    //         'informatike' => self::inf,
    //         'russkomu-yazyku' => self::rus,
    //         'literature' => self::lit,
    //         'obshchestvoznaniju' => self::soc,
    //         'istorii' => self::his,
    //         'anglijskomu-yazyku' => self::eng,
    //         'geografii' => self::geo,
    //         'sochineniju' => self::soch,
    //     };
    // }
}
