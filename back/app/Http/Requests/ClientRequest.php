<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'passport.birthdate' => ['sometimes', 'date_format:Y-m-d'],
            'parent.passport.issued_date' => ['sometimes', 'date_format:Y-m-d']
        ];
    }
}
