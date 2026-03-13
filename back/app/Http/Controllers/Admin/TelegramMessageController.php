<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TelegramMessageResource;
use App\Models\Phone;
use App\Models\TelegramMessage;
use App\Utils\Phone as PhoneUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TelegramMessageController extends Controller
{
    protected $filters = [
        'number' => ['number'],
        'equals' => ['template'],
        'null' => ['status'],
    ];

    protected $mapFilters = [
        'status' => 'telegram_id',
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

    public function store(Request $request)
    {
        $request->validate([
            'phone_id' => ['required', 'exists:phones,id'],
            'text' => ['required', 'string'],
        ]);

        $phone = Phone::findOrFail($request->phone_id);

        $telegramMessage = TelegramMessage::send(
            $phone,
            $request->input('text'),
            user: auth()->user()
        );

        return new TelegramMessageResource($telegramMessage);
    }

    protected function filterNumber(Builder $query, string $number): void
    {
        // Нормализуем номер из UI (+7, скобки, пробелы) перед поиском.
        $number = PhoneUtils::clean($number);
        $query->where('number', 'like', "%{$number}%");
    }
}
