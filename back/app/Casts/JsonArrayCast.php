<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class JsonArrayCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value ? json_decode($value) : [];
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_array($value) && count($value)) {
            // Default behavior: Encode the array to JSON
            return json_encode($value);
        }
        return null;
    }
}
