<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Models\Error;
use Illuminate\Http\Request;

class ErrorController extends Controller
{
    protected $filters = [
        'equals' => ['code']
    ];

    public function index(Request $request)
    {
        $query = Error::query();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, ErrorResource::class);
    }

    public function check()
    {
        Error::check();
    }
}
