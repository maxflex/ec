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
        'equals' => ['phone_id']
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TelegramMessage::query();
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
}
