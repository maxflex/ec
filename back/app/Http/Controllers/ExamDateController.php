<?php

namespace App\Http\Controllers;

use App\Enums\Program;
use App\Models\ExamDate;
use Illuminate\Http\Request;

class ExamDateController extends Controller
{
    protected $filters = [
        'programs' => ['programs'],
    ];

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

    protected function filterPrograms(&$query, $programs)
    {
        $exams = [];
        foreach ($programs as $program) {
            $exams[] = Program::from($program)->getExam();
        }
        $query->whereIn('exam', $exams);
    }
}
