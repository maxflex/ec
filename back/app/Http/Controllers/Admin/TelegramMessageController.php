<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TelegramMessageResource;
use App\Models\TelegramMessage;
use Illuminate\Http\Request;

class TelegramMessageController extends Controller
{
    protected $filters = [
        'equals' => ['number', 'template'],
        'null' => ['status'],
    ];

    protected $mapFilters = [
        'status' => 'telegram_id'
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TelegramMessage::with(['entity']);

        if ($request->has('number')) {
            $query->oldest();
        } else {
            $query->latest();
        }

        $this->filter($request, $query);
        return $this->handleIndexRequest(
            $request,
            $query,
            TelegramMessageResource::class
        );
    }
}
