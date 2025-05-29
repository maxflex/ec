<?php

namespace App\Http\Requests;

use App\Utils\Phone;
use Illuminate\Foundation\Http\FormRequest;

class PubTeacherRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['required', 'mobile'],
            'file' => ['required', 'file', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'укажите ваш телефон',
            'phone.mobile' => 'неверный формат номера',
            'file' => 'загрузите файл с резюме',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Phone::autoCorrectFirstDigit($this->input('phone')),
        ]);
    }
}
