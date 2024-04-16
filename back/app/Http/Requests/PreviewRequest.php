<?php

namespace App\Http\Requests;

use App\Models\{Client, Teacher, Phone};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PreviewRequest extends FormRequest
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
            'id' => ['required', 'integer'],
            'entity_type' => ['required', Rule::in([
                Client::class,
                Teacher::class
            ])]
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $phone = Phone::query()
                    ->where('entity_id', $this->id)
                    ->where('entity_type', $this->entity_type)
                    ->first();
                if ($phone === null) {
                    $validator->errors()->add('phone', 'нет телефона');
                }
            }
        ];
    }
}
