<?php

namespace App\Http\Requests;

use App\Utils\Phone;
use Illuminate\Foundation\Http\FormRequest;

class PubRequestRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', 'mobile'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'укажите ваш телефон',
            'phone.mobile' => 'неверный формат номера',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Phone::autoCorrectFirstDigit($this->input('phone')),
        ]);
    }
}
