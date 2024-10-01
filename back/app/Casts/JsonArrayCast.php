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
        // If the value is an empty array, store `null` in the database
        if (is_array($value) && empty($value)) {
            return null;
        }

        // Otherwise, store the value as a JSON-encoded string
        return json_encode($value);
    }
}
