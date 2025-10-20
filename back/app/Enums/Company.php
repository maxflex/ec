<?php

namespace App\Enums;

enum Company: string
{
    case ip = 'ip';
    case ooo = 'ooo';
    case ano = 'ano';

    public function bankAccount(): array
    {
        return match ($this) {
            self::ip => [
                'Name' => 'ИП Горшкова Анастасия Александровна',
                'BankName' => 'АО "АЛЬФА-БАНК"',
                'BIC' => '044525593',
                'CorrespAcc' => '30101810200000000593',
                'PersonalAcc' => '40802810401400004731',
                'PayeeINN' => '622709802712',
                'KPP' => '',
            ],
            self::ooo => [
                'Name' => 'ООО "ЕГЭ-Центр"',
                'BankName' => 'АО "АЛЬФА-БАНК"',
                'BIC' => '044525593',
                'CorrespAcc' => '30101810200000000593',
                'PersonalAcc' => '40702810801960000153',
                'PayeeINN' => '9701038111',
                'KPP' => '770101001',
            ],
            self::ano => [
                'Name' => 'АНО ДО "Школа будущего"',
                'BankName' => 'ПАО Сбербанк',
                'BIC' => '044525225',
                'CorrespAcc' => '30101810400000000225',
                'PersonalAcc' => '40703810238720001128',
                'PayeeINN' => '9703186517',
                'KPP' => '770301001',
            ],
        };
    }
}
