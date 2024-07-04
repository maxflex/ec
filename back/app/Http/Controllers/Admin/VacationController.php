<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use Illuminate\Http\Request;

class VacationController extends Controller
{
    protected $filters = [
        'year' => ['year']
    ];

    public function index(Request $request)
    {
        $query = Vacation::query();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query);
    }

    public function store(Request $request)
    {
        $vacation = Vacation::where('date', $request->date)->first();
        if ($vacation === null) {
            return Vacation::create($request->all());
        }
        $vacation->delete();
    }

    protected function filterYear(&$query, $year)
    {
        $query->whereYear($year);
    }
}
