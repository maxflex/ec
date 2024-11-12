<b>Доступен новый отчет</b>

{{ $report->client->parent->formatName('first-middle') }}, здравствуйте!

Преподаватель {{ $report->teacher->formatName('initials') }} написал новый отчёт по программе {{ $report->program->getName() }} об ученике {{ $report->client->formatName() }}.

Нажмите, чтобы посмотреть отчёт:
