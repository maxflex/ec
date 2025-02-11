<?php

namespace App\Utils;

use App\Models\Lesson;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AvailableYears
{
    /** @var 'reports'|'schedule' */
    public string $mode;

    private int $clientId;

    private int $teacherId;

    public function __construct(Request $request)
    {
        $this->clientId = $request->client_id;
        $this->teacherId = $request->teacher_id;
        $this->mode = $request->mode;
    }

    public static function get(Request $request): array
    {
        return (new AvailableYears($request))->getData();
    }

    public function getData(): array
    {
        $years = collect();

        if ($this->clientId) {
            switch ($this->mode) {
                case 'schedule':
                    $years = $this->forClientSchedule();
                    break;

                case 'reports':
                    $years = $this->forClientReports();
                    break;
            }
        } elseif ($this->teacherId) {
            switch ($this->mode) {
                case 'schedule':
                    $years = $this->forTeacherSchedule();
                    break;
                case 'reports':
            }
        }

        return $years->unique()->sort()->values()->all();
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

    private function forClientReports(): Collection
    {
        return Report::where('client_id', $this->clientId)
            ->distinct()
            ->pluck('year');
    }

    private function forTeacherSchedule(): Collection
    {
        return Lesson::where('teacher_id', $this->teacherId)
            ->distinct()
            ->pluck('year');
    }
}
