<?php

namespace App\Utils;

use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AvailableYears
{
    /** @var 'reports'|'schedule'|'grades' */
    public string $mode;

    private ?int $clientId;

    private ?int $teacherId;

    public function __construct(Request $request)
    {
        $this->clientId = $request->input('client_id');
        $this->teacherId = $request->input('teacher_id');
        $this->mode = $request->input('mode');
    }

    public static function get(Request $request): array
    {
        return (new AvailableYears($request))->handle();
    }

    public function handle(): array
    {
        $years = collect();

        switch ($this->mode) {
            case 'reports':
                $years = $this->forReports();
                break;

            case 'grades':
                $years = $this->forGrades();
                break;

            case 'schedule':
                if ($this->clientId) {
                    $years = $this->forClientSchedule();
                } elseif ($this->teacherId) {
                    $years = $this->forTeacherSchedule();
                }
                break;
        }

        return $years->unique()->sortDesc()->values()->all();
    }

    private function forReports(): Collection
    {
        return Report::distinct()
            ->when($this->clientId, fn ($q) => $q->where('client_id', $this->clientId))
            ->when($this->teacherId, fn ($q) => $q->where('teacher_id', $this->teacherId))
            ->pluck('year');
    }

    private function forGrades(): Collection
    {
        return Grade::distinct()
            ->when($this->clientId, fn ($q) => $q->where('client_id', $this->clientId))
            ->when($this->teacherId, fn ($q) => $q->where('teacher_id', $this->teacherId))
            ->pluck('year');
    }

    private function forClientSchedule(): Collection
    {
        return collect(DB::select("
            select distinct(c.year) from client_lessons cl
            join contract_version_programs cvp on cvp.id = cl.contract_version_program_id
            join contract_versions cv on cv.id = cvp.contract_version_id
            join contracts c on c.id = cv.contract_id
            where c.client_id = $this->clientId
            union
            select distinct(c.year) from `client_groups` cg
            join contract_version_programs cvp on cvp.id = cg.contract_version_program_id
            join contract_versions cv on cv.id = cvp.contract_version_id
            join contracts c on c.id = cv.contract_id
            where c.client_id = $this->clientId
        "))->pluck('year');
    }

    private function forTeacherSchedule(): Collection
    {
        return Lesson::distinct()
            ->where('teacher_id', $this->teacherId)
            ->pluck('year');
    }
}
