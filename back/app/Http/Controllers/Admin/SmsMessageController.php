<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use Illuminate\Http\Request;

class SmsMessageController extends Controller
{
    protected $filters = [
        'equals' => ['number'],
        'status' => ['status'],
    ];

    public function index(Request $request)
    {
        $query = SmsMessage::latest('created_at');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query);
    }

    protected function filterStatus($query, $delivered)
    {
        $query->where('status', $delivered ? '=' : '<>', 1);
    }
}
