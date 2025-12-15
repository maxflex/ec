<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhoneResource;
use App\Models\Phone;
use App\Models\Representative;
use Illuminate\Http\Request;

/**
 * Нужен для подгрузки доступных номеров телефона для отправки чека
 */
class PhoneController extends Controller
{
    protected $filters = [
        'contractId' => ['contract_id'],
    ];

    public function index(Request $request)
    {
        $query = Phone::query();

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, PhoneResource::class);
    }

    protected function filterContractId($query, $contractId)
    {
        // Используем whereHasMorph вместо комбинации where + whereHas
        $query->whereHasMorph(
            'entity',
            [Representative::class], // Явно указываем класс
            fn ($q) => $q->whereHas('client',
                fn ($q) => $q->whereHas('contracts',
                    fn ($q) => $q->where('id', $contractId)
                )
            )
        );
    }
}
