@php use Carbon\Carbon; @endphp
<html lang="ru">
<head>
    <title>ЕГЭ-Центр</title>
    <style>
        /* Add custom styles here if necessary */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .print__contract h4 {
            text-align: center
        }
    </style>
</head>

<body>
<div class="d-flex">
    <img src="https://cdn.ege-centr.ru/crm/other/print-bar.png" class="print-bar"/>
    <div class="inn-kpp rows">
        <div class="d-flex-center">
            <span>
                ИНН
            </span>
            <div class="box">
                @foreach(str_split(str('9701038111')->padRight(12)) as $char)
                    <div>
                        {{ $char }}
                    </div>
                @endforeach
            </div>
        </div>
        <div class="d-flex-center">
            <span>
                КПП
            </span>
            <div class="box">
                @foreach(str_split(str('770301001')->padRight(9)) as $char)
                    <div>
                        {{ $char }}
                    </div>
                @endforeach
            </div>
            <span style="width: 37px" class="text-center">
                Стр.
            </span>
            <div class="box">
                <div>0</div>
                <div>0</div>
                <div>1</div>
            </div>
        </div>
    </div>
</div>
<div style="padding: 36px 36px 0; line-height: 0" class="box-label font-weight-bold">
    Форма по КНД 1151158
</div>
<div class="text-center font-weight-bold" style="margin-bottom: 10px">
    Справка <br/>
    об оплате образовательных услуг для представления в <br/>
    налоговый орган
</div>
<div class="d-flex-center">
    <span class="box-label">
        Номер справки
    </span>
    <div class="box">
        @foreach(range(1, 12) as $i)
            <div>
                {{ str($seq)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
    <span class="box-label" style="margin-left: 30px">
        Номер корректировки
    </span>
    <div class="box">
        @foreach(str_split('0--') as $i)
            <div>{{$i}}</div>
        @endforeach
    </div>
    <span class="box-label" style="margin-left: 20px">
        Отчетный год
    </span>
    <div class="box">
        @foreach(str_split($year) as $char)
            <div>
                {{ $char }}
            </div>
        @endforeach
    </div>
</div>

<div class="box-label mt">
    Данные образовательной организации/индивидуального предпринимателя, осуществляющего образовательную деятельность:
</div>

<div class="box mt">
    @foreach(range(1, 40) as $i)
        <div>
            {{ str('Общество с ограниченной ответственностью')->charAt($i - 1) }}
        </div>
    @endforeach
</div>
<div class="box mt-6">
    @foreach(range(1, 40) as $i)
        <div>
            {{ str('«ЕГЭ-Центр»')->charAt($i - 1) }}
        </div>
    @endforeach
</div>

<div class="box mt-6">
    @foreach(range(1, 40) as $i)
        <div>
        </div>
    @endforeach
</div>
<div class="box mt-6">
    @foreach(range(1, 40) as $i)
        <div>
        </div>
    @endforeach
</div>
<div class="text-center small">
    (наименование образовательной организации / фамилия, имя, отчество<sup>1</sup> индивидуального предпринимателя)
</div>

<div class="d-flex-center" style="margin-top: 20px">
    <div class="box-label">
        Обучение проводилось по очной форме обучения
    </div>
    <div class="box">
        <div>
            1
        </div>
    </div>
    <div class="yes-no small">
        <div>
            <span> 0 </span> – нет
        </div>
        <div>
            <span> 1 </span> – да
        </div>
    </div>
</div>

<div class="mt box-label">
    Данные физического лица (его супруга/супруги), оплатившего образовательные услуги (далее - налогоплательщик):
</div>
<div class="d-flex-center mt">
    <div class="box-label" style="width: 75px">
        Фамилия
    </div>
    <div class="box">
        @foreach(range(1, 35) as $i)
            <div>
                {{ str($client->parent->last_name)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
</div>
<div class="d-flex-center mt-6">
    <div class="box-label" style="width: 75px">
        Имя
    </div>
    <div class="box">
        @foreach(range(1, 35) as $i)
            <div>
                {{ str($client->parent->first_name)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
</div>
<div class="d-flex-center mt-6">
    <div class="box-label" style="width: 75px">
        Отчество
    </div>
    <div class="box">
        @foreach(range(1, 35) as $i)
            <div>
                {{ str($client->parent->middle_name)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex-center mt-6">
    <div class="box-label" style="width: 75px">
        ИНН<sup>2</sup>
    </div>
    <div class="box">
        @foreach(range(1, 12) as $i)
            <div>
                {{ str($parent_inn)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
    <div class="box-label" style="margin-left: 51px">
        Дата рождения
    </div>
    <div class="box">
        @foreach(str_split(Carbon::parse($parent_birthday)->format('d-m-Y')) as $char)
            @if($char === '-')
                <div class="box-spacer"></div>
            @else
                <div>{{ $char }}</div>
            @endif
        @endforeach
    </div>
</div>

<div class="mt box-label">
    Сведения о документе, удостоверяющем личность:
</div>

<div class="d-flex-center mt">
    <div class="box-label" style="width: 130px">
        Код вида документа
    </div>
    <div class="box">
        <div>2</div>
        <div>1</div>
    </div>
    <div class="box-label" style="margin-left: 94px">
        Серия и номер
    </div>
    <div class="box">
        @foreach(range(1, 20) as $i)
            <div>
                {{ str($client->parent->passport->series . $client->parent->passport->number)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex-center mt">
    <div class="box-label" style="width: 130px">
        Дата выдачи
    </div>
    <div class="box">
        @foreach(str_split(Carbon::parse($client->parent->passport->issued_date)->format('d-m-Y')) as $char)
            @if($char === '-')
                <div class="box-spacer"></div>
            @else
                <div>{{ $char }}</div>
            @endif
        @endforeach
    </div>
</div>

<div class="d-flex-center mt">
    <div class="box-label" style="width: 352px">
        Налогоплательщик и обучаемый являются одним лицом
    </div>
    <div class="box">
        <div>
            0
        </div>
    </div>
    <div class="yes-no small">
        <div>
            <span> 0 </span> – нет
        </div>
        <div>
            <span> 1 </span> – да
        </div>
    </div>
</div>

<div class="d-flex-center mt">
    <div class="box-label" style="width: 352px">
        Сумма расходов на оказанные образовательные услуги
    </div>
    <div class="box">
        @foreach(range(1, 13) as $i)
            <div>
                {{ str($sum)->padLeft(13)->charAt($i - 1) }}
            </div>
        @endforeach
        <div class="box-spacer"></div>
        <div>0</div>
        <div>0</div>
    </div>
</div>

<div class="bottom-zone">
    <div>
        <div class="bottom-zone__title">
            Достоверность и полноту сведений, указанных<br />
            в настоящей справке, подтверждаю:
        </div>
        <div class="box mt">
            @foreach(mb_str_split(str('Колбягина')->padRight(19)) as $char)
                <div>
                    {{$char}}
                </div>
            @endforeach
        </div>
        <div class="box mt-6">
            @foreach(mb_str_split(str('Анастасия')->padRight(19)) as $char)
                <div>
                    {{$char}}
                </div>
            @endforeach
        </div>
        <div class="box mt-6">
            @foreach(mb_str_split(str('Тимофеевна')->padRight(19)) as $char)
                <div>
                    {{$char}}
                </div>
            @endforeach
        </div>
        <div class="text-center small mt-6">
            (фамилия, имя, отчество)
        </div>
        <div class="d-flex-center mt">
            <div class="box-label" style="width: 160px">
                Подпись _____________ Дата
            </div>
            <div class="box">
                @foreach(str_split(now()->format('d-m-Y')) as $char)
                    @if($char === '-')
                        <div class="box-spacer"></div>
                    @else
                        <div>{{ $char }}</div>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="d-flex-center mt">
            <div class="box-label" style="width: 160px">
                Справка составлена на
            </div>
            <div class="box">
                <div></div>
                <div></div>
                <div>2</div>
            </div>
            <div class="box-label" style="margin-left: 20px">
                страницах
            </div>
        </div>
    </div>
    <div>
        <div class="bottom-zone__title">
            Зона QR-кода:
        </div>
    </div>
</div>

<div class="box-label">
   <sup>1</sup> Отчество указывается при наличии (относится ко всем листам документа).
</div>
<div class="box-label">
    <sup>2</sup> ИНН указывается при наличии.
</div>

<div class="d-flex page-break">
    <img src="https://cdn.ege-centr.ru/crm/other/print-bar.png" class="print-bar"/>
    <div class="inn-kpp rows">
        <div class="d-flex-center">
            <span>
                ИНН
            </span>
            <div class="box">
                @foreach(str_split(str('9701038111')->padRight(12)) as $char)
                    <div>
                        {{ $char }}
                    </div>
                @endforeach
            </div>
        </div>
        <div class="d-flex-center">
            <span>
                КПП
            </span>
            <div class="box">
                @foreach(str_split(str('770301001')->padRight(9)) as $char)
                    <div>
                        {{ $char }}
                    </div>
                @endforeach
            </div>
            <span style="width: 37px" class="text-center">
                Стр.
            </span>
            <div class="box">
                <div>0</div>
                <div>0</div>
                <div>2</div>
            </div>
        </div>
    </div>
</div>

<div class="mt box-label" style="margin-top: 40px">
    Данные физического лица, которому оказаны образовательные услуги<sup>1</sup>:
</div>
<div class="d-flex-center mt">
    <div class="box-label" style="width: 75px">
        Фамилия
    </div>
    <div class="box">
        @foreach(range(1, 35) as $i)
            <div>
                {{ str($client->last_name)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
</div>
<div class="d-flex-center mt-6">
    <div class="box-label" style="width: 75px">
        Имя
    </div>
    <div class="box">
        @foreach(range(1, 35) as $i)
            <div>
                {{ str($client->first_name)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
</div>
<div class="d-flex-center mt-6">
    <div class="box-label" style="width: 75px">
        Отчество
    </div>
    <div class="box">
        @foreach(range(1, 35) as $i)
            <div>
                {{ str($client->middle_name)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex-center mt-6">
    <div class="box-label" style="width: 75px">
        ИНН<sup>2</sup>
    </div>
    <div class="box">
        @foreach(range(1, 12) as $i)
            <div>
                {{ str($client_inn)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
    <div class="box-label" style="margin-left: 51px">
        Дата рождения
    </div>
    <div class="box">
        @foreach(str_split(Carbon::parse($client->passport->birthdate)->format('d-m-Y')) as $char)
            @if($char === '-')
                <div class="box-spacer"></div>
            @else
                <div>{{ $char }}</div>
            @endif
        @endforeach
    </div>
</div>

<div class="mt box-label">
    Сведения о документе, удостоверяющем личность:
</div>

<div class="d-flex-center mt">
    <div class="box-label" style="width: 130px">
        Код вида документа
    </div>
    <div class="box">
        <div>2</div>
        <div>1</div>
    </div>
    <div class="box-label" style="margin-left: 94px">
        Серия и номер
    </div>
    <div class="box">
        @foreach(range(1, 20) as $i)
            <div>
                {{ str($client->passport->series . $client->passport->number)->charAt($i - 1)  }}
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex-center mt">
    <div class="box-label" style="width: 130px">
        Дата выдачи
    </div>
    <div class="box">
        @foreach(str_split(Carbon::parse($client_passport_issued_at)->format('d-m-Y')) as $char)
            @if($char === '-')
                <div class="box-spacer"></div>
            @else
                <div>{{ $char }}</div>
            @endif
        @endforeach
    </div>
</div>


<div class="box-label" style="margin-top: 17cm">
    <sup>1</sup> Данные заполняются, если налогоплательщик и обучаемый не являются одним лицом.
</div>
<div class="box-label">
    <sup>2</sup> ИНН указывается при наличии.
</div>

<div class="box-label mt text-center" style="margin-top: 20px">
    Достоверность и полноту сведений, указанных на данной странице, подтверждаю:
</div>

<div class="box-label bottom-zone-2">
    <div>
        <span class="bottom-zone-2__signature" style="color: transparent">
            -----------
        </span> (подпись)
    </div>
    <div>
        <span class="bottom-zone-2__signature">
         {{  now()->format('d.m.Y') }}
        </span>
         (дата)
    </div>
</div>


<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet">

<style>
    body {
        /*font-family: "Open Sans", sans-serif;*/
        /*font-size: 15px;*/
        font-family: "Inter", sans-serif;
        font-optical-sizing: auto;
        font-size: 15px;
        letter-spacing: -0.1px;
    }

    .print-bar {
        width: 171px;
    }

    .box {
        display: flex;
    }

    .bottom-zone-2 {
        display: flex;
        align-items: center;
        justify-content: space-evenly;
        padding-top: 20px;
    }

    .bottom-zone-2__signature {
        font-family: "Courier New", Courier, monospace;
        font-size: 18px;
        border-bottom: 1px solid black;
        display: inline-block;
        margin-right: 6px;
    }

    .box > div {
        border: 1px dotted #d8d8d8;
        width: 18px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;

        font-family: "Courier New", Courier, monospace;
        font-size: 18px;
    }


    .box-spacer {
        border-top: none !important;
        border-bottom: none !important;
        position: relative;
    }

    .box-spacer:before {
        content: '';
        height: 3px;
        width: 3px;
        border-radius: 50%;
        background: black;
        position: absolute;
        top: 10px;
    }

    sup {
        font-size: 9px;
    }

    .small {
        font-size: 11px;
    }

    .box > div:not(:first-child) {
        border-left: none;
    }

    .mt {
        margin-top: 10px
    }

    .mt-6 {
        margin-top: 6px;
    }

    .box-label {
        font-size: 12px;
        margin-right: 10px;
        white-space: nowrap;
    }

    .d-flex {
        display: flex;
        align-items: flex-start;
    }

    .d-flex-center {
        display: flex;
        align-items: center;
    }

    .inn-kpp {
        margin-left: 36px;
    }

    .rows {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .inn-kpp span {
        width: 40px;
    }

    .yes-no {
        line-height: 10px;
        margin-left: 10px;

        span {
            font-family: "Courier New", Courier, monospace;
        }
    }

    .font-weight-bold {
        font-weight: bold;
    }

    .text-center {
        text-align: center;
    }

    .bottom-zone {
        display: flex;
        border-top: 1px solid black;
        margin-top: 20px;
        margin-bottom: 10px;
        height: 9cm;
    }

    .bottom-zone > div {
        flex: 1;
    }
    .bottom-zone > div:first-child {
        border-right: 1px solid black;
    }

    .bottom-zone__title {
        font-weight: bold;
        font-size: 13px;
        text-align: center;
        margin-top: 6px;
    }

    .page-break {
        page-break-before: always;
        padding-top: 20px;
    }

    /*body {*/
    /*    width: 210mm;*/
    /*    min-height: 297mm;*/
    /*    padding: 5mm;*/
    /*    border: 1px #D3D3D3 solid;*/
    /*    border-radius: 5px;*/
    /*    background: white;*/
    /*    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);*/
    /*}*/

    /*@media print {*/
    /*    html, body {*/
    /*        border: none;*/
    /*        box-shadow: none;*/
    /*        padding: initial;*/
    /*        width: initial;*/
    /*    }*/
    /*}*/


</style>
</body>
</html>
