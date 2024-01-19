<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Request as ClientRequest;
use Illuminate\Http\Request;

/**
 * Бред: если назвать RequestController, а не RequestSController,
 * то не работает XDebug
 * Чёртов бред, кучу времени потратил
 */
class RequestsController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientRequest::query();
        $response = $this->handleIndexRequest($request, $query);
        return $response;
    }
}
