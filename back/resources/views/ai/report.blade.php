@php
    use App\Enums\ClientLessonStatus;
@endphp

Ученик: {{ $report->client->first_name }}
Программа: {{ $report->program->getHumanName() }}
Оценка по отчету: {{ $report->grade }}

Посещаемость и пройденные темы:
@foreach($report->clientLessons as $cl)
{{ $cl->lesson->date }} - @if ($cl->status === ClientLessonStatus::absent)не был@elseif (ClientLessonStatus::getLateStatuses()->contains($cl->status))опоздал@if($cl->minutes_late) на {{ $cl->minutes_late }} мин.@endif @elseif (ClientLessonStatus::getOnlineStatuses()->contains($cl->status))был (онлайн)@elseбыл@endif

@if ($cl->lesson->topic)
Тема урока: {{ $cl->lesson->topic }}
@endif

@if ($cl->scores)
Оценки:
@foreach($cl->scores as $s)
{{ $s->score }}@if($s->comment) - {{ $s->comment }}@endif{{ $loop->last ? '' : ', ' }}
@endforeach
@endif

@if ($cl->comment)
Комментарий от преподавателя: {{ $cl->comment }}
@endif
@endforeach
