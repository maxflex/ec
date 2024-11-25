<?php

namespace App\Http\Resources;

use App\Models\ClientLesson;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Contract */
class ContractBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeVersion = $this->active_version;
        $contractPayments = $this->payments->reduce(fn($carry, $p) => $carry + $p->realSum, 0);
        $clientLessons = intval(
            ClientLesson::query()
                ->whereIn('contract_version_program_id', $activeVersion->programs()->pluck('id'))
                ->sum('price')
        );

        $remainder = $contractPayments - $clientLessons;
        $toPay = $activeVersion->sum - $contractPayments;


        return extract_fields($this, [
            'company',
        ], [
            'client' => new PersonResource($this->client),
            'active_version_sum' => $activeVersion->sum,
            'contract_payments' => $contractPayments,
            'client_lessons' => $clientLessons,
            'remainder' => $remainder,
            'to_pay' => $toPay,
            'latest_payment_date' => $this->payments->max('date')
        ]);
    }
}
