{{ $person->formatName() }}, здравствуйте! Завтра {{ date('d.m', strtotime($date)) }} состоится первое занятие.

<b>Расписание:</b>
@foreach($lessons as $lesson)
{{$lesson->timeFormatted}} КАБ-{{$lesson->cabinet->getName()}}: {{$lesson->group->program->getShortName()}}
@endforeach
