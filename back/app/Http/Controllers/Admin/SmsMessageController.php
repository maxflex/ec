<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Utils\Phone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SmsMessageController extends Controller
{
    protected $filters = [
        'number' => ['number'],
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

    protected function filterNumber(Builder $query, string $number): void
    {
        // Нормализуем номер из UI (+7, скобки, пробелы) перед поиском.
        $number = Phone::clean($number);
        $query->where('number', 'like', "%{$number}%");
    }
}
