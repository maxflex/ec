<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SwampListResource;
use App\Models\Swamp;
use Illuminate\Http\Request;

class SwampController extends Controller
{
    protected $filters = [
        'year' => ['year'],
        'program' => ['program'],
    ];

    public function index(Request $request)
    {
        $query = Swamp::query();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, SwampListResource::class);
    }

    protected function filterYear(&$query, $year)
    {
        $query->whereRaw(<<<SQL
            (c.year = ? OR g.year = ?)
        SQL, [
            $year, $year
        ]);
    }

    protected function filterProgram(&$query, $program)
    {
        $query->whereRaw(<<<SQL
            (g.program = ? OR cvp.program = ?)
        SQL, [
            $program, $program
        ]);
    }
}
