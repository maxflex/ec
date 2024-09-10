{{ $clientLesson->contractVersionProgram->contractVersion->contract->client->formatName() }} @if ($clientLesson->status === \App\Enums\ClientLessonStatus::absent)
    пропустил(а) урок
@elseif ($clientLesson->is_remote)
    был(а) удалённо на уроке
@else
    опоздал(а) на {{ $clientLesson->minutes_late }} минут на урок
@endif по программе {{ $clientLesson->contractVersionProgram->program->getName() }} {{ date('d.m.Y', strtotime($clientLesson->lesson->date)) }}