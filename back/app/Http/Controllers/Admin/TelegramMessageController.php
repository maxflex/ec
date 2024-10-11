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
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TelegramMessage::with(['entity'])->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest(
            $request,
            $query,
            TelegramMessageResource::class
        );
    }
}
