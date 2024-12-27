{{ $clientLesson->contractVersionProgram->contractVersion->contract->client->formatName() }}
@if ($clientLesson->status === \App\Enums\ClientLessonStatus::absent) пропустил(а) урок
@elseif (in_array($clientLesson->status, [\App\Enums\ClientLessonStatus::lateOnline, \App\Enums\ClientLessonStatus::presentOnline])) был(а) удалённо на уроке
@elseif ($clientLesson->minutes_late) опоздал(а) на {{ $clientLesson->minutes_late }} минут на урок
@endif по программе {{ $clientLesson->contractVersionProgram->program->getName() }} {{ date('d.m.Y', strtotime($clientLesson->lesson->date)) }}
