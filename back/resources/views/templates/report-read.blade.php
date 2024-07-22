<b>Составитель отчета:</b> {{ $report->teacher->formatNameFull() }}
<b>Ученик:</b> {{ $report->client->formatName() }}
<b>Программа:</b> {{ $report->program->getName() }}

<b>Посещаемость и пройденные темы:</b>
@foreach ($report->contractLessons as $cl)
{{ date('d.m.Y', strtotime($cl->lesson->date)) }} –@if ($cl->status === \App\Enums\ContractLessonStatus::absent) <b>не был</b> @elseif ($cl->status === \App\Enums\ContractLessonStatus::late) опоздал на {{ $cl->minutes_late }} мин.  @else был @endif

@if ($cl->lesson->topic)
<i>{{ $cl->lesson->topic }}</i>
@endif

@endforeach

<b>Отчёт:</b>
{{ $report->homework_comment }}
