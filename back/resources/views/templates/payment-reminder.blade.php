{{ $clientParent->formatName('first-middle') }}, здравствуйте. Напоминаем, что {{ $payment->dateObj->translatedFormat('j F')  }} Вам необходимо внести платеж за обучение в ЕГЭ-Центре в размере {{ number_format($payment->sum, 0, '', ' ') }} руб.

Спасибо. С уважением, учебная часть ЕГЭ-Центра.
