<?php

namespace App\Enums;

enum Program: string
{
    case math9 = 'math9';
    case phys9 = 'phys9';
    case chem9 = 'chem9';
    case bio9 = 'bio9';
    case inf9 = 'inf9';
    case rus9 = 'rus9';
    case lit9 = 'lit9';
    case soc9 = 'soc9';
    case his9 = 'his9';
    case eng9 = 'eng9';
    case geo9 = 'geo9';
    case essay9 = 'essay9';

    case math10 = 'math10';
    case phys10 = 'phys10';
    case chem10 = 'chem10';
    case bio10 = 'bio10';
    case inf10 = 'inf10';
    case rus10 = 'rus10';
    case lit10 = 'lit10';
    case soc10 = 'soc10';
    case his10 = 'his10';
    case eng10 = 'eng10';

    case math11 = 'math11';
    case phys11 = 'phys11';
    case chem11 = 'chem11';
    case bio11 = 'bio11';
    case inf11 = 'inf11';
    case rus11 = 'rus11';
    case lit11 = 'lit11';
    case soc11 = 'soc11';
    case his11 = 'his11';
    case eng11 = 'eng11';
    case geo11 = 'geo11';
    case essay11 = 'essay11';

    case mathExt = 'mathExt';
    case physExt = 'physExt';
    case chemExt = 'chemExt';
    case bioExt = 'bioExt';
    case infExt = 'infExt';
    case rusExt = 'rusExt';
    case litExt = 'litExt';
    case socExt = 'socExt';
    case hisExt = 'hisExt';
    case engExt = 'engExt';
    case geoExt = 'geoExt';

    case mathSchool8 = 'mathSchool8';
    case physSchool8 = 'physSchool8';
    case chemSchool8 = 'chemSchool8';
    case bioSchool8 = 'bioSchool8';
    case infSchool8 = 'infSchool8';
    case rusSchool8 = 'rusSchool8';
    case litSchool8 = 'litSchool8';
    case socSchool8 = 'socSchool8';
    case hisSchool8 = 'hisSchool8';
    case engSchool8 = 'engSchool8';
    case geoSchool8 = 'geoSchool8';

    case mathSchool9 = 'mathSchool9';
    case physSchool9 = 'physSchool9';
    case chemSchool9 = 'chemSchool9';
    case bioSchool9 = 'bioSchool9';
    case infSchool9 = 'infSchool9';
    case rusSchool9 = 'rusSchool9';
    case litSchool9 = 'litSchool9';
    case socSchool9 = 'socSchool9';
    case hisSchool9 = 'hisSchool9';
    case engSchool9 = 'engSchool9';
    case geoSchool9 = 'geoSchool9';

    case mathSchool10 = 'mathSchool10';
    case physSchool10 = 'physSchool10';
    case chemSchool10 = 'chemSchool10';
    case bioSchool10 = 'bioSchool10';
    case infSchool10 = 'infSchool10';
    case rusSchool10 = 'rusSchool10';
    case litSchool10 = 'litSchool10';
    case socSchool10 = 'socSchool10';
    case hisSchool10 = 'hisSchool10';
    case engSchool10 = 'engSchool10';

    case mathSchool11 = 'mathSchool11';
    case physSchool11 = 'physSchool11';
    case chemSchool11 = 'chemSchool11';
    case bioSchool11 = 'bioSchool11';
    case infSchool11 = 'infSchool11';
    case rusSchool11 = 'rusSchool11';
    case litSchool11 = 'litSchool11';
    case socSchool11 = 'socSchool11';
    case hisSchool11 = 'hisSchool11';
    case engSchool11 = 'engSchool11';

    case physSchoolOge = 'physSchoolOge';
    case chemSchoolOge = 'chemSchoolOge';
    case bioSchoolOge = 'bioSchoolOge';
    case infSchoolOge = 'infSchoolOge';
    case litSchoolOge = 'litSchoolOge';
    case socSchoolOge = 'socSchoolOge';
    case hisSchoolOge = 'hisSchoolOge';
    case engSchoolOge = 'engSchoolOge';

    case mathPracticum = 'mathPracticum';
    case physPracticum = 'physPracticum';
    case chemPracticum = 'chemPracticum';
    case bioPracticum = 'bioPracticum';
    case infPracticum = 'infPracticum';
    case rusPracticum = 'rusPracticum';
    case socPracticum = 'socPracticum';
    case hisPracticum = 'hisPracticum';
    case engPracticum = 'engPracticum';
    case geoPracticum = 'geoPracticum';

    case mathBase = 'mathBase';
    case mathProf = 'mathProf';

    /**
     * ID предмета (для переноса из старой БД)
     */
    public static function getById(int $gradeId, int $subjectId): self
    {
        $gradeSubject = join('-', [$gradeId, $subjectId]);
        $program = match ($gradeSubject) {
            '9-1' => self::math9,
            '9-2' => self::phys9,
            '9-3' => self::chem9,
            '9-4' => self::bio9,
            '9-5' => self::inf9,
            '9-6' => self::rus9,
            '9-7' => self::lit9,
            '9-8' => self::soc9,
            '9-9' => self::his9,
            '9-10' => self::eng9,
            '9-11' => self::geo9,
            '9-12' => self::essay9,

            '10-1' => self::math10,
            '10-2' => self::phys10,
            '10-3' => self::chem10,
            '10-4' => self::bio10,
            '10-5' => self::inf10,
            '10-6' => self::rus10,
            '10-7' => self::lit10,
            '10-8' => self::soc10,
            '10-9' => self::his10,
            '10-10' => self::eng10,

            // 10 класс сочинение реально не существует
            '10-12' => self::essay11,

            '11-1' => self::math11,
            '11-2' => self::phys11,
            '11-3' => self::chem11,
            '11-4' => self::bio11,
            '11-5' => self::inf11,
            '11-6' => self::rus11,
            '11-7' => self::lit11,
            '11-8' => self::soc11,
            '11-9' => self::his11,
            '11-10' => self::eng11,
            '11-11' => self::geo11,
            '11-12' => self::essay11,

            '14-1' => self::mathExt,
            '14-2' => self::physExt,
            '14-3' => self::chemExt,
            '14-4' => self::bioExt,
            '14-5' => self::infExt,
            '14-6' => self::rusExt,
            '14-7' => self::litExt,
            '14-8' => self::socExt,
            '14-9' => self::hisExt,
            '14-10' => self::engExt,
            '14-11' => self::geoExt,


            '15-1' => self::mathSchool8,
            '15-2' => self::physSchool8,
            '15-3' => self::chemSchool8,
            '15-4' => self::bioSchool8,
            '15-5' => self::infSchool8,
            '15-6' => self::rusSchool8,
            '15-7' => self::litSchool8,
            '15-8' => self::socSchool8,
            '15-9' => self::hisSchool8,
            '15-10' => self::engSchool8,
            '15-11' => self::geoSchool8,

            '16-1' => self::mathSchool9,
            '16-2' => self::physSchool9,
            '16-3' => self::chemSchool9,
            '16-4' => self::bioSchool9,
            '16-5' => self::infSchool9,
            '16-6' => self::rusSchool9,
            '16-7' => self::litSchool9,
            '16-8' => self::socSchool9,
            '16-9' => self::hisSchool9,
            '16-10' => self::engSchool9,
            '16-11' => self::geoSchool9,

            '16-14' => self::physSchoolOge,
            '16-15' => self::chemSchoolOge,
            '16-16' => self::bioSchoolOge,
            '16-13' => self::infSchoolOge,
            '16-17' => self::litSchoolOge,
            '16-19' => self::socSchoolOge,
            '16-20' => self::hisSchoolOge,
            '16-18' => self::engSchoolOge,

            '17-1' => self::mathSchool10,
            '17-2' => self::physSchool10,
            '17-3' => self::chemSchool10,
            '17-4' => self::bioSchool10,
            '17-5' => self::infSchool10,
            '17-6' => self::rusSchool10,
            '17-7' => self::litSchool10,
            '17-8' => self::socSchool10,
            '17-9' => self::hisSchool10,
            '17-10' => self::engSchool10,

            '18-1' => self::mathSchool11,
            '18-2' => self::physSchool11,
            '18-3' => self::chemSchool11,
            '18-4' => self::bioSchool11,
            '18-5' => self::infSchool11,
            '18-6' => self::rusSchool11,
            '18-7' => self::litSchool11,
            '18-8' => self::socSchool11,
            '18-9' => self::hisSchool11,
            '18-10' => self::engSchool11,

            '19-26' => self::mathPracticum,
            '19-28' => self::physPracticum,
            '19-29' => self::chemPracticum,
            '19-30' => self::bioPracticum,
            '19-31' => self::infPracticum,
            '19-32' => self::rusPracticum,
            '19-27' => self::socPracticum,
            '19-33' => self::hisPracticum,
            '19-34' => self::engPracticum,
            '19-35' => self::geoPracticum,

            '9-36' => self::essay9,

            // ошибка в старой системе
            '19-6' => self::rusPracticum,

            default => throw new \Error("Grade-subject $gradeSubject not exists!")
        };
        return $program;
    }
}
