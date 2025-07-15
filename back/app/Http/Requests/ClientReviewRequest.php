<?php

namespace App\Http\Requests;

use App\Enums\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'program' => ['required', Rule::enum(Program::class)],
            'rating' => ['required', 'integer', 'min:0', 'max:5'],
            'text' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
