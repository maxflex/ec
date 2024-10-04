<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TelegramListResource;
use App\Models\TelegramList;
use Illuminate\Http\Request;

class TelegramListController extends Controller
{
    protected $filters = [
        'equals' => ['status']
    ];

    public function index(Request $request)
    {
        $query = TelegramList::query()
            ->with('event')
            ->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, TelegramListResource::class);
    }

    public function show(TelegramList $telegramList)
    {
        return new TelegramListResource($telegramList);
    }

    public function store(Request $request)
    {
        $telegramList = auth()->user()->entity->telegramLists()->create(
            $request->all(),
        );
        return new TelegramListResource($telegramList);
    }

    public function destroy(TelegramList $telegramList)
    {
        $telegramList->delete();
    }

    public function loadPeople(Request $request)
    {
        return TelegramList::getPeople($request);
    }
}
