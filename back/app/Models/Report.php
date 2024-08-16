<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Enums\TelegramTemplate;
use App\Observers\ReportObserver;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ReportObserver::class)]
class Report extends Model
{
    protected $fillable = [
        'year', 'program', 'price', 'homework_comment',
        'is_moderated', 'is_published', 'client_id'
    ];

    protected $casts = [
        'is_moderated' => 'boolean',
        'is_published' => 'boolean',
        'program' => Program::class,
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getPreviousAttribute()
    {
        return Report::query()
            ->where('teacher_id', $this->teacher_id)
            ->where('client_id', $this->client_id)
            ->where('year', $this->year)
            ->where('program', $this->program)
            ->where('created_at', '<', $this->created_at)
            ->latest()
            ->first();
    }

    public function getLessonsAttribute(): Builder
    {
        $query = Lesson::query()
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'lessons.id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->where('lessons.status', LessonStatus::conducted->value)
            ->where('conducted_at', '<', $this->created_at)
            ->where('teacher_id', $this->teacher_id)
            ->where('c.client_id', $this->client_id)
            ->where('g.year', $this->year)
            ->where('g.program', $this->program);

        $previousReport = $this->previous;
        if ($previousReport !== null) {
            $query->where('lessons.conducted_at', '>', $previousReport->created_at);
        }

        return $query;
    }

    public function getClientLessonsAttribute()
    {
        return ClientLesson::whereIn('id', $this->lessons->pluck('cl.id'))->with('lesson')->get();
    }

    /**
     * Прочитать отчёт
     */
    public function read()
    {
        TelegramMessage::sendTemplate(
            TelegramTemplate::reportRead,
            $this->client->parent->phones()->withTelegram()->get()->all(),
            ['report' => $this]
        );
    }

    public function scopePrepareForUnion($query)
    {
        return $query->selectRaw(<<<SQL
            id,
            teacher_id,
            client_id,
            year,
            program,
            is_moderated,
            is_published,
            created_at,
            price,
            NULL as lessons_count
        SQL);
    }
}
