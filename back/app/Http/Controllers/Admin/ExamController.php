<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Exam;
use App\Http\Controllers\Controller;

class ExamController extends Controller
{
    public function __invoke()
    {
        $data = [];

        foreach (Exam::cases() as $exam) {
            $data[] = [
                'exam' => $exam->value,
                'programs' => $exam->getPrograms(),
            ];
        }

        return paginate($data);
    }
}
