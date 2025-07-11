<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum Program: string
{
    case math9 = 'math9';
    case math10 = 'math10';
    case math11 = 'math11';
    case mathBaseExternal = 'mathBaseExternal';
    case mathProfExternal = 'mathProfExternal';
    case mathSchool8 = 'mathSchool8';
    case mathSchool9 = 'mathSchool9';
    case mathBaseSchool10 = 'mathBaseSchool10';
    case mathProfSchool10 = 'mathProfSchool10';
    case mathBaseSchool11 = 'mathBaseSchool11';
    case mathProfSchool11 = 'mathProfSchool11';

    case phys9 = 'phys9';
    case phys10 = 'phys10';
    case phys11 = 'phys11';
    case physExternal = 'physExternal';
    case physSchool8 = 'physSchool8';
    case physSchool9 = 'physSchool9';
    case physSchool10 = 'physSchool10';
    case physSchool11 = 'physSchool11';

    case chem9 = 'chem9';
    case chem10 = 'chem10';
    case chem11 = 'chem11';
    case chemExternal = 'chemExternal';
    case chemSchool8 = 'chemSchool8';
    case chemSchool9 = 'chemSchool9';
    case chemSchool10 = 'chemSchool10';
    case chemSchool11 = 'chemSchool11';

    case bio9 = 'bio9';
    case bio10 = 'bio10';
    case bio11 = 'bio11';
    case bioExternal = 'bioExternal';
    case bioSchool8 = 'bioSchool8';
    case bioSchool9 = 'bioSchool9';
    case bioSchool10 = 'bioSchool10';
    case bioSchool11 = 'bioSchool11';

    case inf9 = 'inf9';
    case inf10 = 'inf10';
    case inf11 = 'inf11';
    case infExternal = 'infExternal';
    case infSchool8 = 'infSchool8';
    case infSchool9 = 'infSchool9';
    case infSchool10 = 'infSchool10';
    case infSchool11 = 'infSchool11';

    case rus9 = 'rus9';
    case rus10 = 'rus10';
    case rus11 = 'rus11';
    case rusExternal = 'rusExternal';
    case rusSchool8 = 'rusSchool8';
    case rusSchool9 = 'rusSchool9';
    case rusSchool10 = 'rusSchool10';
    case rusSchool11 = 'rusSchool11';

    case lit9 = 'lit9';
    case lit10 = 'lit10';
    case lit11 = 'lit11';
    case litExternal = 'litExternal';
    case litSchool8 = 'litSchool8';
    case litSchool9 = 'litSchool9';
    case litSchool10 = 'litSchool10';
    case litSchool11 = 'litSchool11';

    case soc9 = 'soc9';
    case soc10 = 'soc10';
    case soc11 = 'soc11';
    case socExternal = 'socExternal';
    case socSchool8 = 'socSchool8';
    case socSchool9 = 'socSchool9';
    case socSchool10 = 'socSchool10';
    case socSchool11 = 'socSchool11';

    case his9 = 'his9';
    case his10 = 'his10';
    case his11 = 'his11';
    case hisExternal = 'hisExternal';
    case hisSchool8 = 'hisSchool8';
    case hisSchool9 = 'hisSchool9';
    case hisSchool10 = 'hisSchool10';
    case hisSchool11 = 'hisSchool11';

    case eng9 = 'eng9';
    case eng10 = 'eng10';
    case eng11 = 'eng11';
    case engExternal = 'engExternal';
    case engSchool8 = 'engSchool8';
    case engSchool9 = 'engSchool9';
    case engSchool10 = 'engSchool10';
    case engSchool11 = 'engSchool11';

    case geo9 = 'geo9';
    case geo10 = 'geo10';
    case geo11 = 'geo11';
    case geoExternal = 'geoExternal';
    case geoSchool8 = 'geoSchool8';
    case geoSchool9 = 'geoSchool9';
    case geoSchool10 = 'geoSchool10';
    case geoSchool11 = 'geoSchool11';

    case izl9 = 'izl9';
    case soch11 = 'soch11';

    case infOgeSchool9 = 'infOgeSchool9';
    case physOgeSchool9 = 'physOgeSchool9';
    case chemOgeSchool9 = 'chemOgeSchool9';
    case bioOgeSchool9 = 'bioOgeSchool9';
    case litOgeSchool9 = 'litOgeSchool9';
    case engOgeSchool9 = 'engOgeSchool9';
    case socOgeSchool9 = 'socOgeSchool9';
    case hisOgeSchool9 = 'hisOgeSchool9';

    case geoOgeSchool9 = 'geoOgeSchool9';
    case engSpoken = 'engSpoken';
    case mathPract = 'mathPract';
    case physPract = 'physPract';
    case chemPract = 'chemPract';
    case bioPract = 'bioPract';
    case infPract = 'infPract';
    case rusPract = 'rusPract';
    case litPract = 'litPract';
    case socPract = 'socPract';
    case hisPract = 'hisPract';
    case engPract = 'engPract';
    case geoPract = 'geoPract';

    /**
     * @return Collection<int, Program>
     */
    public static function getAllExternal(): Collection
    {
        return collect(Program::cases())
            ->filter(fn (Program $p) => $p->getDirection() === Direction::external);
    }

    public function getDirection(): Direction
    {
        return match ($this) {
            self::eng10 => Direction::courses10,
            self::bio10 => Direction::courses10,
            self::geo10 => Direction::courses10,
            self::inf10 => Direction::courses10,
            self::his10 => Direction::courses10,
            self::lit10 => Direction::courses10,
            self::math10 => Direction::courses10,
            self::soc10 => Direction::courses10,
            self::rus10 => Direction::courses10,
            self::phys10 => Direction::courses10,
            self::chem10 => Direction::courses10,
            self::engSchool10 => Direction::school10,
            self::bioSchool10 => Direction::school10,
            self::infSchool10 => Direction::school10,
            self::hisSchool10 => Direction::school10,
            self::litSchool10 => Direction::school10,
            self::mathBaseSchool10 => Direction::school10,
            self::mathProfSchool10 => Direction::school10,
            self::chemSchool10 => Direction::school10,
            self::geoSchool10 => Direction::school10,
            self::socSchool10 => Direction::school10,
            self::rusSchool10 => Direction::school10,
            self::physSchool10 => Direction::school10,
            self::eng11 => Direction::courses11,
            self::bio11 => Direction::courses11,
            self::geo11 => Direction::courses11,
            self::inf11 => Direction::courses11,
            self::his11 => Direction::courses11,
            self::lit11 => Direction::courses11,
            self::math11 => Direction::courses11,
            self::soc11 => Direction::courses11,
            self::rus11 => Direction::courses11,
            self::phys11 => Direction::courses11,
            self::chem11 => Direction::courses11,
            self::engSchool11 => Direction::school11,
            self::bioSchool11 => Direction::school11,
            self::geoSchool11 => Direction::school11,
            self::infSchool11 => Direction::school11,
            self::hisSchool11 => Direction::school11,
            self::litSchool11 => Direction::school11,
            self::mathBaseSchool11 => Direction::school11,
            self::mathProfSchool11 => Direction::school11,
            self::socSchool11 => Direction::school11,
            self::rusSchool11 => Direction::school11,
            self::physSchool11 => Direction::school11,
            self::chemSchool11 => Direction::school11,
            self::engSchool8 => Direction::school8,
            self::bioSchool8 => Direction::school8,
            self::geoSchool8 => Direction::school8,
            self::infSchool8 => Direction::school8,
            self::hisSchool8 => Direction::school8,
            self::litSchool8 => Direction::school8,
            self::mathSchool8 => Direction::school8,
            self::socSchool8 => Direction::school8,
            self::rusSchool8 => Direction::school8,
            self::physSchool8 => Direction::school8,
            self::chemSchool8 => Direction::school8,
            self::eng9 => Direction::courses9,
            self::bio9 => Direction::courses9,
            self::geo9 => Direction::courses9,
            self::inf9 => Direction::courses9,
            self::his9 => Direction::courses9,
            self::lit9 => Direction::courses9,
            self::math9 => Direction::courses9,
            self::soc9 => Direction::courses9,
            self::rus9 => Direction::courses9,
            self::phys9 => Direction::courses9,
            self::chem9 => Direction::courses9,
            self::engSchool9 => Direction::school9,
            self::engOgeSchool9 => Direction::school9,
            self::bioSchool9 => Direction::school9,
            self::bioOgeSchool9 => Direction::school9,
            self::geoSchool9 => Direction::school9,
            self::geoOgeSchool9 => Direction::school9,
            self::infSchool9 => Direction::school9,
            self::infOgeSchool9 => Direction::school9,
            self::hisSchool9 => Direction::school9,
            self::hisOgeSchool9 => Direction::school9,
            self::litSchool9 => Direction::school9,
            self::litOgeSchool9 => Direction::school9,
            self::mathSchool9 => Direction::school9,
            self::socSchool9 => Direction::school9,
            self::socOgeSchool9 => Direction::school9,
            self::rusSchool9 => Direction::school9,
            self::physSchool9 => Direction::school9,
            self::physOgeSchool9 => Direction::school9,
            self::chemSchool9 => Direction::school9,
            self::chemOgeSchool9 => Direction::school9,
            self::engPract => Direction::practicum,
            self::bioPract => Direction::practicum,
            self::geoPract => Direction::practicum,
            self::infPract => Direction::practicum,
            self::hisPract => Direction::practicum,
            self::litPract => Direction::practicum,
            self::mathPract => Direction::practicum,
            self::socPract => Direction::practicum,
            self::rusPract => Direction::practicum,
            self::physPract => Direction::practicum,
            self::chemPract => Direction::practicum,
            self::engSpoken => Direction::coursesExtra,
            self::izl9 => Direction::coursesExtra,
            self::soch11 => Direction::coursesExtra,
            self::engExternal => Direction::external,
            self::bioExternal => Direction::external,
            self::geoExternal => Direction::external,
            self::infExternal => Direction::external,
            self::hisExternal => Direction::external,
            self::litExternal => Direction::external,
            self::mathBaseExternal => Direction::external,
            self::mathProfExternal => Direction::external,
            self::socExternal => Direction::external,
            self::rusExternal => Direction::external,
            self::physExternal => Direction::external,
            self::chemExternal => Direction::external,
        };
    }

    /**
     * @return Collection<int, Program>
     */
    public static function getAllSchool(): Collection
    {
        return collect(Program::cases())
            ->filter(fn (Program $p) => in_array($p->getDirection(), [
                Direction::school11,
                Direction::school10,
                Direction::school9,
                Direction::school8,
            ]));
    }

    // /**
    //  * @return array<int, Program>
    //  */
    // public static function getAllSchool(): array
    // {
    //     return collect(Program::cases())
    //         ->filter(fn (Program $p) => in_array($p->getDirection(), [
    //             Direction::school8,
    //             Direction::school9,
    //             Direction::school10,
    //             Direction::school11,
    //         ]))
    //         ->all();
    // }

    public function getName(): string
    {
        return match ($this) {
            self::bio10 => '10 курсы БИО',
            self::bio11 => '11 курсы БИО',
            self::bio9 => '9 курсы БИО',
            self::bioExternal => 'экстернат БИО',
            self::bioOgeSchool9 => '9 школа БИО ОГЭ',
            self::bioPract => 'практикум БИО',
            self::bioSchool10 => '10 школа  БИО',
            self::bioSchool11 => '11 школа БИО',
            self::bioSchool8 => '8 школа БИО',
            self::bioSchool9 => '9 школа БИО',
            self::chem10 => '10 курсы ХИМ',
            self::chem11 => '11 курсы ХИМ',
            self::chem9 => '9 курсы ХИМ',
            self::chemExternal => 'экстернат ХИМ',
            self::chemOgeSchool9 => '9 школа ХИМ ОГЭ',
            self::chemPract => 'практикум ХИМ',
            self::chemSchool10 => '10 школа  ХИМ',
            self::chemSchool11 => '11 школа ХИМ',
            self::chemSchool8 => '8 школа ХИМ',
            self::chemSchool9 => '9 школа ХИМ',
            self::eng10 => '10 курсы АНГ',
            self::eng11 => '11 курсы АНГ',
            self::eng9 => '9 курсы АНГ',
            self::engExternal => 'экстернат АНГ',
            self::engOgeSchool9 => '9 школа АНГ ОГЭ',
            self::engPract => 'практикум АНГ',
            self::engSchool10 => '10 школа  АНГ',
            self::engSchool11 => '11 школа АНГ',
            self::engSchool8 => '8 школа АНГ',
            self::engSchool9 => '9 школа АНГ',
            self::engSpoken => 'разговорный английский',
            self::geo11 => '11 курсы ГЕО',
            self::geoExternal => 'экстернат ГЕО',
            self::geoOgeSchool9 => '9 школа ГЕО ОГЭ',
            self::geoSchool8 => '8 школа ГЕО',
            self::geoSchool9 => '9 школа ГЕО',
            self::his10 => '10 курсы ИСТ',
            self::his11 => '11 курсы ИСТ',
            self::his9 => '9 курсы ИСТ',
            self::hisExternal => 'экстернат ИСТ',
            self::hisOgeSchool9 => '9 школа ИСТ ОГЭ',
            self::hisPract => 'практикум ИСТ',
            self::hisSchool10 => '10 школа  ИСТ',
            self::hisSchool11 => '11 школа ИСТ',
            self::hisSchool8 => '8 школа ИСТ',
            self::hisSchool9 => '9 школа ИСТ',
            self::inf10 => '10 курсы ИНФ',
            self::inf11 => '11 курсы ИНФ',
            self::inf9 => '9 курсы ИНФ',
            self::infExternal => 'экстернат ИНФ',
            self::infOgeSchool9 => '9 школа ИНФ ОГЭ',
            self::infSchool10 => '10 школа  ИНФ',
            self::infSchool11 => '11 школа ИНФ',
            self::infSchool8 => '8 школа ИНФ',
            self::infSchool9 => '9 школа ИНФ',
            self::izl9 => 'собеседование',
            self::lit10 => '10 курсы ЛИТ',
            self::lit11 => '11 курсы ЛИТ',
            self::litExternal => 'экстернат ЛИТ',
            self::litOgeSchool9 => '9 школа ЛИТ ОГЭ',
            self::litPract => 'практикум ЛИТ',
            self::litSchool10 => '10 школа  ЛИТ',
            self::litSchool11 => '11 школа ЛИТ',
            self::litSchool8 => '8 школа ЛИТ',
            self::litSchool9 => '9 школа ЛИТ',
            self::math10 => '10 курсы МАТ',
            self::math11 => '11 курсы МАТ',
            self::math9 => '9 курсы МАТ',
            self::mathBaseExternal => 'экстернат МАТ-Б',
            self::mathProfExternal => 'экстернат МАТ-П',
            self::mathPract => 'практикум МАТ',
            self::mathBaseSchool10 => '10 школа МАТ-Б',
            self::mathProfSchool10 => '10 школа МАТ-П',
            self::mathBaseSchool11 => '11 школа МАТ-Б',
            self::mathProfSchool11 => '11 школа МАТ-П',
            self::mathSchool8 => '8 школа МАТ',
            self::mathSchool9 => '9 школа МАТ',
            self::phys10 => '10 курсы ФИЗ',
            self::phys11 => '11 курсы ФИЗ',
            self::phys9 => '9 курсы ФИЗ',
            self::physExternal => 'экстернат ФИЗ',
            self::physOgeSchool9 => '9 школа ФИЗ ОГЭ',
            self::physPract => 'практикум ФИЗ',
            self::physSchool10 => '10 школа ФИЗ',
            self::physSchool11 => '11 школа ФИЗ',
            self::physSchool8 => '8 школа ФИЗ',
            self::physSchool9 => '9 школа ФИЗ',
            self::rus10 => '10 курсы РУС',
            self::rus11 => '11 курсы РУС',
            self::rus9 => '9 курсы РУС',
            self::rusExternal => 'экстернат РУС',
            self::rusPract => 'практикум РУС',
            self::rusSchool10 => '10 школа РУС',
            self::rusSchool11 => '11 школа РУС',
            self::rusSchool8 => '8 школа РУС',
            self::rusSchool9 => '9 школа РУС',
            self::soc10 => '10 курсы ОБЩ',
            self::soc11 => '11 курсы ОБЩ',
            self::soc9 => '9 курсы ОБЩ',
            self::socExternal => 'экстернат ОБЩ',
            self::soch11 => 'сочинение',
            self::socOgeSchool9 => '9 школа ОБЩ ОГЭ',
            self::socPract => 'практикум ОБЩ',
            self::socSchool10 => '10 школа ОБЩ',
            self::socSchool11 => '11 школа ОБЩ',
            self::socSchool8 => '8 школа ОБЩ',
            self::socSchool9 => '9 школа ОБЩ',
            self::lit9 => '9 курсы ЛИТ',
            self::geo9 => '9 курсы ГЕО',
            self::geo10 => '10 курсы ГЕО',
            self::geoSchool10 => '10 школа ГЕО',
            self::geoSchool11 => '11 школа ГЕО',
            self::infPract => 'практикум ИНФ',
            self::geoPract => 'практикум ГЕО',
        };
    }

    public function getShortName(): string
    {
        return match ($this) {
            self::eng10 => '10К-АНГ',
            self::bio10 => '10К-БИО',
            self::geo10 => '10К-ГЕО',
            self::inf10 => '10К-ИНФ',
            self::his10 => '10К-ИСТ',
            self::lit10 => '10К-ЛИТ',
            self::math10 => '10К-МАТ',
            self::soc10 => '10К-ОБЩ',
            self::rus10 => '10К-РУС',
            self::phys10 => '10К-ФИЗ',
            self::chem10 => '10К-ХИМ',
            self::engSchool10 => '10Ш-АНГ',
            self::bioSchool10 => '10Ш-БИО',
            self::infSchool10 => '10Ш-ИНФ',
            self::hisSchool10 => '10Ш-ИСТ',
            self::litSchool10 => '10Ш-ЛИТ',
            self::mathBaseSchool10 => '10Ш-МАТ-Б',
            self::mathProfSchool10 => '10Ш-МАТ-П',
            self::chemSchool10 => '10Ш-ХИМ',
            self::geoSchool10 => '10Ш-ГЕО',
            self::socSchool10 => '10Ш-ОБЩ',
            self::rusSchool10 => '10Ш-РУС',
            self::physSchool10 => '10Ш-ФИЗ',
            self::eng11 => '11К-АНГ',
            self::bio11 => '11К-БИО',
            self::geo11 => '11К-ГЕО',
            self::inf11 => '11К-ИНФ',
            self::his11 => '11К-ИСТ',
            self::lit11 => '11К-ЛИТ',
            self::math11 => '11К-МАТ',
            self::soc11 => '11К-ОБЩ',
            self::rus11 => '11К-РУС',
            self::phys11 => '11К-ФИЗ',
            self::chem11 => '11К-ХИМ',
            self::engSchool11 => '11Ш-АНГ',
            self::bioSchool11 => '11Ш-БИО',
            self::geoSchool11 => '11Ш-ГЕО',
            self::infSchool11 => '11Ш-ИНФ',
            self::hisSchool11 => '11Ш-ИСТ',
            self::litSchool11 => '11Ш-ЛИТ',
            self::mathBaseSchool11 => '11Ш-МАТ-Б',
            self::mathProfSchool11 => '11Ш-МАТ-П',
            self::socSchool11 => '11Ш-ОБЩ',
            self::rusSchool11 => '11Ш-РУС',
            self::physSchool11 => '11Ш-ФИЗ',
            self::chemSchool11 => '11Ш-ХИМ',
            self::engSchool8 => '8Ш-АНГ',
            self::bioSchool8 => '8Ш-БИО',
            self::geoSchool8 => '8Ш-ГЕО',
            self::infSchool8 => '8Ш-ИНФ',
            self::hisSchool8 => '8Ш-ИСТ',
            self::litSchool8 => '8Ш-ЛИТ',
            self::mathSchool8 => '8Ш-МАТ',
            self::socSchool8 => '8Ш-ОБЩ',
            self::rusSchool8 => '8Ш-РУС',
            self::physSchool8 => '8Ш-ФИЗ',
            self::chemSchool8 => '8Ш-ХИМ',
            self::eng9 => '9К-АНГ',
            self::bio9 => '9К-БИО',
            self::geo9 => '9К-ГЕО',
            self::inf9 => '9К-ИНФ',
            self::his9 => '9К-ИСТ',
            self::lit9 => '9К-ЛИТ',
            self::math9 => '9К-МАТ',
            self::soc9 => '9К-ОБЩ',
            self::rus9 => '9К-РУС',
            self::phys9 => '9К-ФИЗ',
            self::chem9 => '9К-ХИМ',
            self::engSchool9 => '9Ш-АНГ',
            self::engOgeSchool9 => '9Ш-АНГ-ОГЭ',
            self::bioSchool9 => '9Ш-БИО',
            self::bioOgeSchool9 => '9Ш-БИО-ОГЭ',
            self::geoSchool9 => '9Ш-ГЕО',
            self::geoOgeSchool9 => '9Ш-ГЕО-ОГЭ',
            self::infSchool9 => '9Ш-ИНФ',
            self::infOgeSchool9 => '9Ш-ИНФ-ОГЭ',
            self::hisSchool9 => '9Ш-ИСТ',
            self::hisOgeSchool9 => '9Ш-ИСТ-ОГЭ',
            self::litSchool9 => '9Ш-ЛИТ',
            self::litOgeSchool9 => '9Ш-ЛИТ-ОГЭ',
            self::mathSchool9 => '9Ш-МАТ',
            self::socSchool9 => '9Ш-ОБЩ',
            self::socOgeSchool9 => '9Ш-ОБЩ-ОГЭ',
            self::rusSchool9 => '9Ш-РУС',
            self::physSchool9 => '9Ш-ФИЗ',
            self::physOgeSchool9 => '9Ш-ФИЗ-ОГЭ',
            self::chemSchool9 => '9Ш-ХИМ',
            self::chemOgeSchool9 => '9Ш-ХИМ-ОГЭ',
            self::engPract => 'ПР-АНГ',
            self::bioPract => 'ПР-БИО',
            self::geoPract => 'ПР-ГЕО',
            self::infPract => 'ПР-ИНФ',
            self::hisPract => 'ПР-ИСТ',
            self::litPract => 'ПР-ЛИТ',
            self::mathPract => 'ПР-МАТ',
            self::socPract => 'ПР-ОБЩ',
            self::rusPract => 'ПР-РУС',
            self::physPract => 'ПР-ФИЗ',
            self::chemPract => 'ПР-ХИМ',
            self::engSpoken => 'Р-АНГ',
            self::izl9 => 'СОБЕС',
            self::soch11 => 'СОЧ',
            self::engExternal => 'Э-АНГ',
            self::bioExternal => 'Э-БИО',
            self::geoExternal => 'Э-ГЕО',
            self::infExternal => 'Э-ИНФ',
            self::hisExternal => 'Э-ИСТ',
            self::litExternal => 'Э-ЛИТ',
            self::mathBaseExternal => 'Э-МАТ-Б',
            self::mathProfExternal => 'Э-МАТ-П',
            self::socExternal => 'Э-ОБЩ',
            self::rusExternal => 'Э-РУС',
            self::physExternal => 'Э-ФИЗ',
            self::chemExternal => 'Э-ХИМ',
        };
    }

    public function getExam(): ?Exam
    {
        return match ($this) {
            self::bio11 => Exam::egeBio,
            self::bioSchool11 => Exam::egeBio,
            self::bioPract => Exam::egeBio,
            self::bioExternal => Exam::egeBio,
            self::chem11 => Exam::egeChem,
            self::chemSchool11 => Exam::egeChem,
            self::chemPract => Exam::egeChem,
            self::chemExternal => Exam::egeChem,
            self::eng11 => Exam::egeEng,
            self::engSchool11 => Exam::egeEng,
            self::engPract => Exam::egeEng,
            self::engExternal => Exam::egeEng,
            self::geo11 => Exam::egeGeo,
            self::geoSchool11 => Exam::egeGeo,
            self::geoPract => Exam::egeGeo,
            self::geoExternal => Exam::egeGeo,
            self::his11 => Exam::egeHis,
            self::hisSchool11 => Exam::egeHis,
            self::hisPract => Exam::egeHis,
            self::hisExternal => Exam::egeHis,
            self::inf11 => Exam::egeInf,
            self::infSchool11 => Exam::egeInf,
            self::infPract => Exam::egeInf,
            self::infExternal => Exam::egeInf,
            self::lit11 => Exam::egeLit,
            self::litSchool11 => Exam::egeLit,
            self::litPract => Exam::egeLit,
            self::litExternal => Exam::egeLit,
            self::math11 => Exam::egeMathProf,
            self::mathBaseSchool11 => Exam::egeMathBase,
            self::mathProfSchool11 => Exam::egeMathProf,
            self::mathPract => Exam::egeMathProf,
            self::mathBaseExternal => Exam::egeMathBase,
            self::mathProfExternal => Exam::egeMathProf,
            self::phys11 => Exam::egePhys,
            self::physSchool11 => Exam::egePhys,
            self::physPract => Exam::egePhys,
            self::physExternal => Exam::egePhys,
            self::rus11 => Exam::egeRus,
            self::rusSchool11 => Exam::egeRus,
            self::rusPract => Exam::egeRus,
            self::rusExternal => Exam::egeRus,
            self::soc11 => Exam::egeSoc,
            self::socSchool11 => Exam::egeSoc,
            self::socPract => Exam::egeSoc,
            self::socExternal => Exam::egeSoc,
            self::bio9 => Exam::ogeBio,
            self::bioSchool9 => Exam::ogeBio,
            self::bioOgeSchool9 => Exam::ogeBio,
            self::chem9 => Exam::ogeChem,
            self::chemSchool9 => Exam::ogeChem,
            self::chemOgeSchool9 => Exam::ogeChem,
            self::eng9 => Exam::ogeEng,
            self::engSchool9 => Exam::ogeEng,
            self::engOgeSchool9 => Exam::ogeEng,
            self::geo9 => Exam::ogeGeo,
            self::geoSchool9 => Exam::ogeGeo,
            self::geoOgeSchool9 => Exam::ogeGeo,
            self::his9 => Exam::ogeHis,
            self::hisSchool9 => Exam::ogeHis,
            self::hisOgeSchool9 => Exam::ogeHis,
            self::inf9 => Exam::ogeInf,
            self::infSchool9 => Exam::ogeInf,
            self::infOgeSchool9 => Exam::ogeInf,
            self::lit9 => Exam::ogeLit,
            self::litSchool9 => Exam::ogeLit,
            self::litOgeSchool9 => Exam::ogeLit,
            self::math9 => Exam::ogeMath,
            self::mathSchool9 => Exam::ogeMath,
            self::phys9 => Exam::ogePhys,
            self::physSchool9 => Exam::ogePhys,
            self::physOgeSchool9 => Exam::ogePhys,
            self::rus9 => Exam::ogeRus,
            self::rusSchool9 => Exam::ogeRus,
            self::soc9 => Exam::ogeSoc,
            self::socSchool9 => Exam::ogeSoc,
            self::socOgeSchool9 => Exam::ogeSoc,
            self::engSpoken => Exam::engSpoken,
            self::izl9 => Exam::izl9,
            self::soch11 => Exam::soch11,
            default => null
        };
    }

    /**
     * Всего 2 варианта - 55 минут (8-9 классы школа) и 125 (все остальные)
     */
    public function getDuration(): int
    {
        return match ($this) {
            self::mathSchool8 => 55,
            self::physSchool8 => 55,
            self::chemSchool8 => 55,
            self::bioSchool8 => 55,
            self::hisSchool8 => 55,
            self::socSchool8 => 55,
            self::rusSchool8 => 55,
            self::litSchool8 => 55,
            self::engSchool8 => 55,
            self::infSchool8 => 55,
            self::geoSchool8 => 55,
            self::mathSchool9 => 55,
            self::physSchool9 => 55,
            self::chemSchool9 => 55,
            self::bioSchool9 => 55,
            self::hisSchool9 => 55,
            self::socSchool9 => 55,
            self::rusSchool9 => 55,
            self::litSchool9 => 55,
            self::engSchool9 => 55,
            self::infSchool9 => 55,
            self::geoSchool9 => 55,
            self::physOgeSchool9 => 55,
            self::chemOgeSchool9 => 55,
            self::bioOgeSchool9 => 55,
            self::hisOgeSchool9 => 55,
            self::socOgeSchool9 => 55,
            self::litOgeSchool9 => 55,
            self::engOgeSchool9 => 55,
            self::infOgeSchool9 => 55,
            self::geoOgeSchool9 => 55,
            default => 125
        };
    }
}
