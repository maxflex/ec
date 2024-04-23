<?php

namespace App\Enums;

enum Branch: string
{
    case ohr = 'ohr';
    case trg = 'trg';
    case pvn = 'pvn';
    case bgt = 'bgt';
    case izm = 'izm';
    case opl = 'opl';
    case rpt = 'rpt';
    case skl = 'skl';
    case orh = 'orh';
    case ann = 'ann';
    case per = 'per';
    case klg = 'klg';
    case brt = 'brt';
    case str = 'str';
    case vld = 'vld';
    case bel = 'bel';
    case bib = 'bib';
    case svi = 'svi';
    case nag = 'nag';
    case sok = 'sok';
    case pla = 'pla';
    case vod = 'vod';

    public static function getById(int $id): self
    {
        return match ($id) {
            1 => self::ohr,
            2 => self::pvn,
            3 => self::bgt,
            5 => self::izm,
            6 => self::opl,
            7 => self::rpt,
            8 => self::skl,
            9 => self::orh,
            11 => self::ann,
            12 => self::per,
            13 => self::klg,
            14 => self::brt,
            15 => self::str,
            16 => self::vld,
            17 => self::bel,
            18 => self::bib,
            19 => self::svi,
            20 => self::nag,
            21 => self::sok,
            22 => self::pla,
            23 => self::vod,
            24 => self::trg,
        };
    }
}
