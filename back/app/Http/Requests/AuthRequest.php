<?php

namespace App\Http\Requests;

use App\Models\Phone;
use App\Utils\VerificationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AuthRequest extends FormRequest
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
            'code' => ['sometimes', 'string']
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $phone = Phone::auth($this->phone);
                if ($phone === null) {
                    $validator->errors()->add('phone', 'кандидаты <> 1');
                }
                if ($this->has('code')) {
                    $verified = VerificationService::verifyCode(
                        $phone,
                        $this->code
                    );
                    if (!$verified) {
                        $validator->errors()->add('code', 'неверный код подтверждения');
                    }
                }
            }
        ];
    }
}
