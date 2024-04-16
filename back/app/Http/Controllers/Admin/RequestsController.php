<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use App\Models\Request as ClientRequest;
use Illuminate\Http\Request;

/**
 * Бред: если назвать RequestController, а не RequestSController,
 * то не работает XDebug. Чёртов бред, кучу времени потратил
 */
class RequestsController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientRequest::query()
            ->with('phones', 'responsibleUser')
            ->latest();
        return $this->handleIndexRequest($request, $query, RequestResource::class);
    }
}
