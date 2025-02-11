{{ $teacher->formatName('first-middle')  }}, здравствуйте.

Пожалуйста, проведите занятия в системе ЕГЭ-Центра:

@foreach($lessons as $lesson)
    – занятие от {{ $lesson->dateObj->translatedFormat('j M') }} ({{$lesson->group->program->getShortName()}})
@endforeach

Спасибо. С уважением, учебная часть ЕГЭ-Центра.
