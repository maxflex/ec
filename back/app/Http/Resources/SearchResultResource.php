<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ClientParent;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        [$className, $id] = explode('-', $this->resource['document']['id']);
        $class = "App\\Models\\$className";
        $model = $class::find($id);

        $extra = [
            'entity_type' => $class,
            'phones' => PhoneListResource::collection($model->phones)
        ];

        switch ($class) {
            case ClientParent::class:
                $extra['client_id'] = $model->client_id;
                $contracts = $model->client->contracts;

            case Client::class:
                $contracts = $contracts ?? $model->contracts;
                $extra = [
                    ...$extra,
                    // Дубль из Phone::auth
                    'is_active' => $contracts->where('year', 2024)->count() > 0,
                    'contract_versions' => ContractVersionResource::collection(
                        $contracts->sortByDesc('id')->values()->map(fn($c) => $c->getActiveVersion())
                    ),
                ];
                break;

            case Teacher::class:
                $extra = [
                    ...$extra,
                    'status' => $model->status,
                    'subjects' => $model->subjects,
                ];
        }

        return extract_fields($model, [
            'first_name', 'last_name', 'middle_name', 'photo_url'
        ], $extra);
    }
}
