<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamDate;
use Illuminate\Http\Request;

class ExamDateController extends Controller
{
    public function index(Request $request)
    {
        $query = ExamDate::query();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query);
    }

    public function update(ExamDate $examDate, Request $request)
    {
        $examDate->update($request->all());
        return $examDate;
    }
}
