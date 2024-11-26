<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ClientParent;
use App\Models\Pass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecurityPassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Pass) {
            return extract_fields($this, [
                'date'
            ], [
                'name' => $this->comment,
                'type' => 'person',
            ]);
        }
        $class = get_class($this->resource);
        return extract_fields($this, [
        ], [
            'birthday' => $this->when(
                $class === Client::class && ((object)$this->passport)?->birthdate,
                fn() => $this->passport->birthdate
            ),
            'name' => $this->formatName('full'),
            'type' => match ($class) {
                Client::class => 'client',
                ClientParent::class => 'parent',
                Teacher::class => 'teacher',
                User::class => 'user',
            }
        ]);
    }
}
