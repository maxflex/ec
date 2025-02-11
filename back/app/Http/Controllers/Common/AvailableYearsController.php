<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Utils\AvailableYears;
use DB;
use Illuminate\Http\Request;

/**
 * Получить все возможные годы для срезки
 */
class AvailableYearsController extends Controller
{
    public function __invoke(Request $request)
    {
        return AvailableYears::get($request);

        if ($request->has('client_id')) {
            $clientId = intval($request->client_id);
            $years = collect(DB::select("
                select distinct(c.year) from client_lessons cl
                join contract_version_programs cvp on cvp.id = cl.contract_version_program_id
                join contract_versions cv on cv.id = cvp.contract_version_id
                join contracts c on c.id = cv.contract_id
                where c.client_id = $clientId
                union
                select distinct(c.year) from `client_groups` cg
                join contract_version_programs cvp on cvp.id = cg.contract_version_program_id
                join contract_versions cv on cv.id = cvp.contract_version_id
                join contracts c on c.id = cv.contract_id
                where c.client_id = $clientId
            "))->pluck('year');
        }
        if ($request->has('teacher_id')) {
            $years = Lesson::query()
                ->where('teacher_id', $request->teacher_id)
                ->join('groups', 'groups.id', '=', 'lessons.group_id')
                ->select('year')
                ->groupBy('year')
                ->pluck('year');
        }

        return $years->unique()->sort()->values()->all();
        //        return [2015, 2016, 2017, 2018 2019, 2020, 2021];
        //        $request->user()->authorizeRoles(['admin']);
    }
}
