<?php

namespace App\Http\Requests;

use App\Models\Phone;
use App\Utils\VerificationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class VerifyCodeRequest extends FormRequest
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
            'phone_id' => ['required', 'exists:phones,id'],
            'code' => ['required', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $phone = Phone::find($this->phone_id);
                $verified = VerificationService::verifyCode($phone, $this->code);
                if (!$verified) {
                    $validator->errors()->add('code', 'неверный код подтверждения');
                }
            }
        ];
    }
}
