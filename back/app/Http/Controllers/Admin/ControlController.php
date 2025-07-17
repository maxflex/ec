<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientLessonStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ControlGradesResource;
use App\Http\Resources\ControlLkResource;
use App\Models\Client;
use Illuminate\Http\Request;

/**
 * Контроль ЛК, занятий, оценок
 */
class ControlController extends Controller
{
    protected $query;

    public function __construct(Request $request)
    {
        // доступные ко входу в ЛК
        $this->query = Client::canLogin()->whereHas(
            'contracts',
            fn ($q) => $q->where('year', $request->year)
        );
    }

    /**
     * Контроль занятий
     */
    public function lessons(Request $request)
    {
        $this->query
            ->leftJoin('contracts as c',
                fn ($join) => $join
                    ->on('clients.id', '=', 'c.client_id')
                    ->where('c.year', $request->year)
            )
            ->leftJoin('contract_versions as cv', fn ($join) => $join
                ->on('cv.contract_id', '=', 'c.id')
                ->where('cv.is_active', true)
            )
            ->leftJoin('contract_version_programs as cvp', 'cvp.contract_version_id', '=', 'cv.id')
            ->leftJoin('client_lessons as cl', 'cl.contract_version_program_id', '=', 'cvp.id')
            ->selectRaw('
                clients.id, clients.last_name, clients.first_name, clients.middle_name,
                CAST(SUM(IF(cl.status IN (?, ?), 1, 0)) AS UNSIGNED) as online_count,
                CAST(SUM(IF(cl.status IN (?, ?), 1, 0)) AS UNSIGNED) as late_count,
                CAST(SUM(IF(cl.status = ?, 1, 0)) AS UNSIGNED) as absent_count,
                COUNT(DISTINCT cl.id) as lessons_count
            ', [
                ...ClientLessonStatus::getOnlineStatuses()->pluck('value'),
                ...ClientLessonStatus::getLateStatuses()->pluck('value'),
                ClientLessonStatus::absent->value,
            ])
            ->orderByRaw('last_name, first_name')
            ->groupBy('clients.id');

        return paginate($this->query->get());
    }

    /**
     * Контроль ЛК
     */
    public function lk(Request $request)
    {
        $this->query
            ->with(['phones', 'parent.phones', 'reports']) // 'contracts.versions.programs'
            ->with('logs', fn ($q) => $q->whereRaw('created_at >= NOW() - INTERVAL 3 MONTH'))
            ->withCount('comments')
            ->orderByRaw('last_name, first_name');

        return $this->handleIndexRequest(
            $request,
            $this->query,
            ControlLkResource::class
        );
    }

    /**
     * Контроль оценок
     */
    public function grades(Request $request)
    {
        $year = $request->year;

        $this->query
            ->with('grades', fn ($q) => $q->where('year', $year))
            ->with('reports', fn ($q) => $q
                ->whereNotNull('grade')
                ->where('year', $year)
            )
            ->with('contracts',
                fn ($q) => $q
                    ->where('year', $year)
                    ->with('versions', fn ($v) => $v
                        ->where('is_active', true)
                        ->with('programs.clientLessons', fn ($q) => $q->whereNotNull('scores'))
                    )
            )
            ->selectRaw('
                clients.id, clients.last_name, clients.first_name, clients.middle_name,
                clients.mark_sheet
            ')
            ->orderByRaw('last_name, first_name');

        $data = ControlGradesResource::collection($this->query->get());

        return paginate($data);
    }
}
