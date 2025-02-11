Уважаемый(ая) {{ $key === 'clients' ? $receiver->first_name : $receiver->formatName('first-middle')  }}, здравствуйте.

Напоминаем, что завтра ({{ $tomorrow->translatedFormat('j F') }}) есть изменения в графике обучения ЕГЭ-Центра:

@foreach($lessons as $lesson)
    – урок в {{ $lesson->timeFormatted }} по программе {{ $lesson->group->program->getShortName() }} - @if($lesson->is_unplanned)состоится внеплановое занятие в кабинете К-{{ $lesson->cabinet->getName() }}@elseотменен@endif

@endforeach

С уважением, учебная часть ЕГЭ-Центра.
