<?php

namespace App\Enums;

enum Subject
{
    case math;
    case phys;
    case chem;
    case bio;
    case inf;
    case rus;
    case lit;
    case soc;
    case his;
    case eng;
    case geo;
    case soch;

    public function text(): string
    {
        return match ($this) {
            static::math => 'математика',
            static::phys => 'физика',
            static::chem => 'химия',
            static::bio => 'биология',
            static::inf => 'информатика',
            static::rus => 'русский язык',
            static::lit => 'литература',
            static::soc => 'обществознание',
            static::his => 'история',
            static::eng => 'английский язык',
            static::geo => 'география',
            static::soch => 'сочинение',
        };
    }

    public function dative(): string
    {
        return match ($this) {
            static::math => 'математике',
            static::phys => 'физике',
            static::chem => 'химии',
            static::bio => 'биологии',
            static::inf => 'информатике',
            static::rus => 'русскому языку',
            static::lit => 'литературе',
            static::soc => 'обществознанию',
            static::his => 'истории',
            static::eng => 'английскому языку',
            static::geo => 'географии',
            static::soch => 'сочинению',
        };
    }

    public function accusative(): string
    {
        return match ($this) {
            static::math => 'математику',
            static::phys => 'физику',
            static::chem => 'химию',
            static::bio => 'биологию',
            static::inf => 'информатику',
            static::rus => 'русский язык',
            static::lit => 'литературу',
            static::soc => 'обществознание',
            static::his => 'историю',
            static::eng => 'английский язык',
            static::geo => 'географию',
            static::soch => 'сочинение',
        };
    }

    public function instrumental(): string
    {
        return match ($this) {
            static::math => 'математикой',
            static::phys => 'физикой',
            static::chem => 'химией',
            static::bio => 'биологией',
            static::inf => 'информатикой',
            static::rus => 'русским языком',
            static::lit => 'литературой',
            static::soc => 'обществознанием',
            static::his => 'историей',
            static::eng => 'английским',
            static::geo => 'географией',
            static::soch => 'сочинением',
        };
    }

    public function short(): string
    {
        return match ($this) {
            static::math => 'МАТ',
            static::phys => 'ФИЗ',
            static::chem => 'ХИМ',
            static::bio => 'БИО',
            static::inf => 'ИНФ',
            static::rus => 'РУС',
            static::lit => 'ЛИТ',
            static::soc => 'ОБЩ',
            static::his => 'ИСТ',
            static::eng => 'АНГ',
            static::geo => 'ГЕО',
            static::soch => 'СОЧ',
        };
    }

    /**
     * для генерации отзыва
     * (русский язык стал / обществознание стало) самым любимым
     */
    public function became(): string
    {
        return match ($this) {
            static::math => 'стала',
            static::phys => 'стала',
            static::chem => 'стала',
            static::bio => 'стала',
            static::inf => 'стала',
            static::rus => 'стал',
            static::lit => 'стала',
            static::soc => 'стало',
            static::his => 'стала',
            static::eng => 'стал',
            static::geo => 'стала',
            static::soch => 'стало',
        };
    }

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
