<?php

namespace App\Http\Requests;

use App\Models\{Client, Teacher};
use Illuminate\Foundation\Http\FormRequest;


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
            'client_id' => ['required_without:teacher_id', 'exists:clients,id'],
            'teacher_id' => ['required_without:client_id', 'exists:teachers,id'],
        ];
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'entity_id' => $this->client_id ?? $this->teacher_id,
            'entity_type' => $this->client_id ? Client::class : Teacher::class,
        ]);
        $this->offsetUnset('client_id');
        $this->offsetUnset('teacher_id');
    }
}
