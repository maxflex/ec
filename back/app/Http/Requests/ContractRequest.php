<?php

namespace App\Http\Requests;

use App\Enums\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Создание новой цепи договора
 * Или добавление версии к договору
 */
class ContractRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sum' => ['required', 'numeric'],
            'contract.company' => ['required', Rule::enum(Company::class)],
            'programs.*.lessons_planned' => ['required', 'numeric'],
            'programs.*.prices.*.price' => ['required', 'numeric'],
            'programs.*.prices.*.lessons' => ['required', 'numeric'],
            'contract.client_id' => ['required', 'exists:clients,id'],
            'apply_move_groups' => ['sometimes', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
