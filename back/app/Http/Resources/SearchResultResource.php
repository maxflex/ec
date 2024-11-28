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
        $document = $this->resource['document'];
        [$className, $id] = explode('-', $document['id']);
        $class = "App\\Models\\$className";
        $model = $class::find($id);

        $extra = [
            'is_active' => $document['is_active'],
            'entity_type' => $class,
            'phones' => PhoneResource::collection($model->phones)
        ];

        switch ($class) {
            case ClientParent::class:
                $extra['client_id'] = $model->client_id;
                $contracts = $model->client->contracts;

            case Client::class:
                $contracts = $contracts ?? $model->contracts;
                $extra = [
                    ...$extra,
                    'contract_versions' => ContractVersionResource::collection(
                        $contracts->sortByDesc('id')->values()->map(fn($c) => $c->active_version)
                    ),
                ];
                break;

            case Teacher::class:
                $extra = [
                    ...$extra,
                    'status' => $model->status,
                    'subjects' => $model->subjects,
                ];

            case \App\Models\Request::class:
                $extra = [
                    ...$extra,
                    'request' => new RequestListResource($model),
                ];
        }

        return extract_fields($model, [
            'first_name', 'last_name', 'middle_name', 'photo_url',
        ], $extra);
    }
}
