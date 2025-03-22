{{ $client->first_name  }}, здравствуйте!

У вас было {{ plural($lessonsCount, 'занятие', 'занятия', 'занятий') }} с преподавателем {{ $teacher->formatName('initials') }} по программе "{{ $program->getName() }}"

Пожалуйста, оцените, как всё прошло:

