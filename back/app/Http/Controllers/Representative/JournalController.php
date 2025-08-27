<?php

namespace App\Http\Controllers\Representative;

use App\Enums\LessonStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\JournalResource;
use App\Models\ClientLesson;
use App\Models\Group;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->has('available_years')) {
            return $this->getYears();
        }

        // до начала занятий
        $oldLessons = Group::whereClient(auth()->user()->client_id)
            ->where('year', $request->year)
            ->get()
            ->map(fn ($group) => $group->lessons()->with(['teacher', 'group'])
                ->where('status', LessonStatus::conducted)
                ->whereDoesntHave(
                    'clientLessons.contractVersionProgram.contractVersion.contract',
                    fn ($q) => $q->where('client_id', auth()->user()->client_id))
                ->get()
            )
            ->flatten()
            ->map(fn ($lesson) => (object) [
                'lesson' => $lesson,
                'comment' => null,
                'status' => null,
                'minutes_late' => null,
                'scores' => [],
            ]);

        // актуальное расписание
        $clientLessons = ClientLesson::query()
            ->join('lessons as l', 'l.id', '=', 'client_lessons.lesson_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join(
                'contract_version_programs as cvp',
                'cvp.id', '=', 'client_lessons.contract_version_program_id'
            )
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->where('g.year', $request->year)
            ->where('c.client_id', auth()->user()->client_id)
            ->selectRaw('client_lessons.*, cvp.program')
            ->with('lesson')
            ->get();

        $result = $oldLessons->concat($clientLessons)->sortBy('lesson.date')->values();

        return paginate(
            JournalResource::collection($result)
        );
    }

    private function getYears()
    {
        // до начала занятий
        $oldLessons = Group::whereClient(auth()->user()->client_id)
            ->whereHas('lessons', fn ($q) => $q
                ->where('status', LessonStatus::conducted)
                ->whereDoesntHave(
                    'clientLessons.contractVersionProgram.contractVersion.contract',
                    fn ($q) => $q->where('client_id', auth()->user()->client_id))
            )
            ->distinct()
            ->pluck('year');

        // актуальное расписание
        $clientLessons = ClientLesson::query()
            ->join('lessons as l', 'l.id', '=', 'client_lessons.lesson_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join(
                'contract_version_programs as cvp',
                'cvp.id', '=', 'client_lessons.contract_version_program_id'
            )
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->where('c.client_id', auth()->user()->client_id)
            ->distinct()
            ->pluck('g.year');

        return $oldLessons
            ->merge($clientLessons)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }
}
