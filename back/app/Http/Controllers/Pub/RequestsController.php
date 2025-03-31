<?php

namespace App\Http\Controllers\Pub;

use App\Enums\Direction;
use App\Http\Controllers\Controller;
use App\Http\Requests\PubRequestRequest;
use App\Models\Phone;
use App\Models\Request as ClientRequest;
use App\Utils\VerificationService;
use Illuminate\Http\Request;

class RequestsController extends Controller
{
    /**
     * Заявка с сайта
     */
    public function store(PubRequestRequest $request)
    {
        $clientRequest = new ClientRequest($request->all());
        $clientRequest->direction = Direction::fromIncomingRequest($request);
        $clientRequest->ip = $request->ip();
        $clientRequest->save();

        $phone = $clientRequest->phones()->create([
            'number' => $request->input('phone'),
            'comment' => $request->input('name'),
        ]);

        VerificationService::sendCode($phone, true);

        // на фронте нужен только ID созданной заявки
        return [
            'id' => $clientRequest->id,
        ];
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'request_id' => ['required', 'exists:requests,id'],
        ]);

        $clientRequest = ClientRequest::find($request->input('request_id'));
        $phone = $clientRequest->phones()->first();

        abort_unless(
            VerificationService::verifyCode($phone, $request->input('code')), 422
        );

        $clientRequest->is_verified = true;
        $clientRequest->save();

        // смотрим, есть ли подтверждённая заявка с этим номером телефона
        // за последние 14 дней – в зависимости от этого отправляем событие конверсии
        return [
            'send_conversion' => ! ClientRequest::query()
                ->where('id', '<>', $clientRequest->id)
                ->where('is_verified', true)
                ->where('created_at', '>=', now()->subDays(14))
                ->whereHas('phones', fn ($q) => $q->where('number', $phone->number))
                ->exists(),
        ];
    }

    /**
     *  "branch_id": 1,
     *  "grade": 11,
     *  "form": "courses",
     *  "otherCourse": null,
     *  "phone": "9252727210",
     *  "name": "Max",
     *  "subjects": [ 1, 2 ],
     *  "yandex_id": null
     *  "google_id": null
     */
}
