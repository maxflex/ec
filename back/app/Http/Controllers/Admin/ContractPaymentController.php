<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Company;
use App\Enums\ContractPaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContractPaymentResource;
use App\Models\ContractPayment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContractPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = ContractPayment::query()
            ->with(['user', 'contract'])
            ->latest();

        return $this->handleIndexRequest(
            $request,
            $query,
            ContractPaymentResource::class
        );
    }

    public function show(ContractPayment $contractPayment)
    {
        return new ContractPaymentResource($contractPayment);
    }

    public function store(Request $request)
    {
        $request->validate([
            'contract_id' => ['required', 'exists:contracts,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'sum' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::enum(ContractPaymentMethod::class)],
            'receipt_number' => ['sometimes', 'phone'],
        ]);

        $contractPayment = ContractPayment::make($request->all());

        // Логика проверки обязательности чека
        // 1. Компания НЕ "ООО" (значит ИП или АНО)
        $isReceiptRequiredCompany = $contractPayment->contract->company !== Company::ooo;

        // 2. Метод оплаты: СБП Онлайн ИЛИ Счет
        $isReceiptRequiredMethod = in_array($contractPayment->method, [
            ContractPaymentMethod::sbpOnline,
            ContractPaymentMethod::bill,
        ]);

        // Если условия совпали, а чека нет — ошибка
        abort_if(
            $isReceiptRequiredCompany && $isReceiptRequiredMethod && ! $request->receipt_number,
            422,
            'Для ИП и АНО при оплате по Счету или СБП Онлайн указание номера чека (receipt_number) обязательно.'
        );

        $contractPayment->save();

        return new ContractPaymentResource($contractPayment->fresh());
    }

    public function update(ContractPayment $contractPayment, Request $request)
    {
        $contractPayment->update($request->all());

        return new ContractPaymentResource($contractPayment);
    }

    // Платежи нельзя удалять, т.к. отправляется чек
    // public function destroy(ContractPayment $contractPayment)
    // {
    //     $contractPayment->delete();
    // }
}
