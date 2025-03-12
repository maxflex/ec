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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&display=swap"
      rel="stylesheet">
<style>
    .print-bar {
        width: 171px;
    }

    .anonymous-pro-regular {
        font-family: "Anonymous Pro", monospace;
        font-weight: 400;
        font-style: normal;
    }

    .print-box {
        display: flex;
    }

    .print-box > div {
        border: 1px dotted black;
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .print-box > div:not(:first-child) {
        border-left: none;
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
        gap: 8px;
    }

    .inn-kpp span {
        width: 50px;
    }
</style>
<div class="d-flex">
    <img src="https://cdn.ege-centr.ru/crm/other/print-bar.png" class="print-bar" />
    <div class="inn-kpp rows">
        <div class="d-flex-center">
            <span>
                ИНН
            </span>
            <div class="print-box">
                @foreach(range(1, 12) as $i)
                    <div>
                        {{ $inn[$i] ?? '' }}
                    </div>
                @endforeach
            </div>
        </div>
        <div class="d-flex-center">
            <span>
                КПП
            </span>
            <div class="print-box">
                @foreach(range(1, 9) as $i)
                    <div>
                    </div>
                @endforeach
            </div>
            <span style="width: 42px">
                Стр.
            </span>
            <div class="print-box">
                <div>0</div>
                <div>0</div>
                <div>1</div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
