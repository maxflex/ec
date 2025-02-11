<b>Составитель отчета:</b> {{ $report->teacher->formatName('full') }}
<b>Ученик:</b> {{ $report->client->formatName() }}
<b>Программа:</b> {{ $report->program->getName() }}

<b>Посещаемость и пройденные темы:</b>
@foreach ($report->clientLessons as $cl)
{{ date('d.m.Y', strtotime($cl->lesson->date)) }} – @if ($cl->status === \App\Enums\ClientLessonStatus::absent) <b>не был</b> @elseif ($cl->status === \App\Enums\ClientLessonStatus::late) опоздал на {{ $cl->minutes_late }} мин. @else был @endif @if ($cl->lesson->topic)({{ $cl->lesson->topic }})@endif

@endforeach


<b>Выполнение домашнего задания:</b>
{{ $report->homework_comment }}

<b>Способность усваивать новый материал:</b>
{{ $report->cognitive_ability_comment }}

<b>Текущий уровень знаний:</b>
{{ $report->knowledge_level_comment }}

<b>Рекомендации родителям:</b>
{{ $report->recommendation_comment }}

<b>Общая оценка по отчёту: {{$report->grade}} </b>

-----

Вы можете ознакомиться с копией текущего отчета в личном кабинете ученика https://lk.ege-centr.ru. Если у Вас возникли вопросы - пожалуйста, звоните +7 (495) 646-85-92. С уважением, учебная часть ЕГЭ-Центра.
