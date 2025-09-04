{{ $person->formatNameDefault() }}, здравствуйте! Напоминаем, что послезавтра ({{ $date }}) состоится первое занятие в ЕГЭ-Центре.

<b>Расписание на {{ $date }}:</b>
@foreach($lessons as $lesson)
{{$lesson->timeFormatted}} – {{$lesson->group->program->getHumanName()}}, кабинет {{$lesson->cabinet->getName()}}, преподаватель {{ $lesson->teacher->formatName('full')  }}
@endforeach

Расписание в остальные дни смотрите в приложении в боте ЕГЭ-Центра.
