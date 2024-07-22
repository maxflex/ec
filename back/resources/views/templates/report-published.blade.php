<b>Доступен новый отчет</b>

{{ $report->client->parent->formatNameFirstMiddle() }}, здравствуйте!

Преподаватель {{ $report->teacher->formatNameInitials() }} написал новый отчёт по программе {{ $report->program->getName() }} об ученике {{ $report->client->formatName() }}.

Нажмите, чтобы посмотреть отчёт:
