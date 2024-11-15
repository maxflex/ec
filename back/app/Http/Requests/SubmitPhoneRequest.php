<?php

namespace App\Http\Requests;

use App\Models\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitPhoneRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'phone'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $phone = Phone::auth($this->number);
                if ($phone === null) {
                    $validator->errors()->add('number', 'кандидаты <> 1');
                }
            }
        ];
    }
}
