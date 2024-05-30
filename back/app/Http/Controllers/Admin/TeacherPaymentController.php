<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherPayment;
use Illuminate\Http\Request;

class TeacherPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherPayment::query()
            ->with('teacher')
            ->latest();
        return $this->handleIndexRequest($request, $query);
    }
}
