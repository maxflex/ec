<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Telegram;
use App\Http\Controllers\Controller;
use App\Http\Resources\TelegramMessageResource;
use App\Models\Phone;
use App\Models\TelegramMessage;
use Illuminate\Http\Request;

class TelegramMessageController extends Controller
{
    protected $filters = [
        'type' => ['type'],
        'equals' => ['phone_id'],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TelegramMessage::with(['phone', 'phone.entity', 'user']);
        if (!$request->has('phone_id')) {
            $query->latest();
        }
        $this->filter($request, $query);
        return $this->handleIndexRequest(
            $request,
            $query,
            TelegramMessageResource::class
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'phone_id' => ['required', 'exists:phones,id'],
            'text' => ['required', 'string']
        ]);
        $phone = Phone::find($request->phone_id);
        $message = Telegram::sendMessage(
            $phone->telegram_id,
            view('telegram.admin-message', ['text' => $request->text]),
            'HTML',
        );
        $telegramMessage = auth()->user()->entity->telegramMessages()->create([
            'id' => $message->getMessageId(),
            ...$request->all()
        ]);
        return new TelegramMessageResource($telegramMessage);
    }

    public function bulkStore(Request $request)
    {
        $entryId = TelegramMessage::max('entry_id') ?? 0;
        $entryId++;

        foreach ($request->participants as $p) {
            $phone = Phone::query()
                ->where('entity_id', $p['id'])
                ->where('entity_type', $p['entity_type'])
                ->whereNotNull('telegram_id')
                ->first();
            $message = Telegram::sendMessage(
                $phone->telegram_id,
                view('telegram.admin-message', ['text' => $request->text]),
                'HTML',
            );
            auth()->user()->entity->telegramMessages()->create([
                'id' => $message->getMessageId(),
                'phone_id' => $phone->id,
                'entry_id' => $entryId,
                'text' => $request->text,
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    protected function filterType(&$query, $type)
    {
        $type ? $query->whereNotNull('entry_id') : $query->whereNull('entry_id');
    }
}
