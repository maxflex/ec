@php use App\Enums\ClientLessonStatus; @endphp
{{ $clientLesson->contractVersionProgram->contractVersion->contract->client->representative->formatName('first-middle') }}, здравствуйте.

{{ $clientLesson->contractVersionProgram->contractVersion->contract->client->formatName() }} @if ($clientLesson->status === ClientLessonStatus::absent)
    пропустил(а) урок
@elseif (in_array($clientLesson->status, [
    ClientLessonStatus::lateOnline,
    ClientLessonStatus::presentOnline,
]))
    был(а) удалённо на уроке
@elseif ($clientLesson->minutes_late)
    опоздал(а) на {{ $clientLesson->minutes_late }} минут на урок
@endif по программе {{ $clientLesson->contractVersionProgram->program->getName() }} {{ date('d.m.Y', strtotime($clientLesson->lesson->date)) }} в {{ $clientLesson->lesson->timeFormatted }}
