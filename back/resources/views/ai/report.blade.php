@php
    use App\Enums\ClientLessonStatus;
    use App\Models\Report;

    /** @var Report $report */
    /** @var string $letter */
@endphp

-----НАЧАЛО ОТЧЕТА ({{ $letter }})-----

Оценка по отчету: {{ $report->grade }}

Посещаемость и пройденные темы:

@foreach($report->clientLessons as $cl)
{{ $cl->lesson->date }} - @if ($cl->status === ClientLessonStatus::absent)не был@elseif (ClientLessonStatus::getLateStatuses()->contains($cl->status))опоздал@if($cl->minutes_late) на {{ $cl->minutes_late }} мин.@endif @elseif (ClientLessonStatus::getOnlineStatuses()->contains($cl->status))был (онлайн)@elseбыл@endif. @if ($cl->lesson->topic)Тема занятия: {{ $cl->lesson->topic }}@endif @if ($cl->scores)Оценки ученика: @foreach($cl->scores as $s){{ $s->score }}@if($s->comment) ({{ $s->comment }})@endif{{ $loop->last ? '.' : ', ' }}@endforeach @endif @if ($cl->comment)Комментарий от преподавателя: {{ $cl->comment }}@endif


@endforeach

-----НАЧАЛО ТЕКСТА ПРЕПОДАВАТЕЛЯ ({{ $letter }})-----

@if ($report->comment)
{{ $report->comment }}
@else
Выполнение домашнего задания: {{ $report->homework_comment }}

Способность усваивать новый материал: {{ $report->cognitive_ability_comment }}

Текущий уровень знаний: {{ $report->knowledge_level_comment }}

Рекомендации родителям: {{$report->recommendation_comment}}

@endif

-----КОНЕЦ ТЕКСТА ПРЕПОДАВАТЕЛЯ ({{ $letter }})-----

-----КОНЕЦ ОТЧЕТА ({{ $letter }})-----
