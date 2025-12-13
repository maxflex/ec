<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TelegramListResource;
use App\Models\TelegramList;
use Illuminate\Http\Request;

class TelegramListController extends Controller
{
    protected $filters = [
        'equals' => ['status'],
    ];

    public function index(Request $request)
    {
        $query = TelegramList::query()
            ->with(['event', 'user', 'telegramMessages.entity'])
            ->orderByRaw('IFNULL(scheduled_at, created_at) DESC');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, TelegramListResource::class);
    }

    public function show(TelegramList $telegramList, Request $request)
    {
        // чтобы recipients подгрузились в TelegramListResource
        $request->merge(['recipients' => 1]);

        return new TelegramListResource($telegramList);
    }

    public function store(Request $request)
    {
        $telegramList = TelegramList::create($request->all());

        return new TelegramListResource($telegramList);
    }

    public function destroy(TelegramList $telegramList)
    {
        $telegramList->delete();
    }
}
