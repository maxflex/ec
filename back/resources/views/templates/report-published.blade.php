<b>Доступен новый отчет</b>

{{ $report->client->representative->formatName('first-middle') }}, здравствуйте!

Преподаватель {{ $report->teacher->formatName('initials') }} написал новый отчёт по программе {{ $report->program->getName() }} об ученике {{ $report->client->formatName() }}.

<a href="https://t.me/{{ config('telegram.bot') }}/?startapp=reports_{{ $report->id }}">Посмотреть отчет</a>

