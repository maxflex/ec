<?php

namespace App\Http\Requests;

use App\Models\{Client, Teacher, User, Phone};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LoginRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
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
            'phone' => ['required', 'phone'],
            // 'password' => ['required', 'in:asd']
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (Phone::auth($this->phone) === null) {
                    $validator->errors()->add('phone', "не удалос");
                }
            }
        ];
    }
}
