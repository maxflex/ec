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

        $common = [
            'is_active' => $document['is_active'],
            'entity_type' => $class,
            'phones' => PhoneResource::collection($model->phones),
        ];

        switch ($class) {
            case ClientParent::class:
                $model = $model->client;

            case Client::class:
                $extra = [
                    'client' => [
                        'directions' => $model->directions,
                        'contracts' => extract_fields_array($model->contracts, [
                            'year',
                        ]),
                    ],
                ];
                break;

            case Teacher::class:
                $extra = [
                    'teacher' => [
                        'status' => $model->status,
                        'subjects' => $model->subjects,
                    ],
                ];
                break;

            default:
                $extra = [
                    'request' => new RequestListResource($model),
                ];
        }

        return extract_fields($model, [
            'first_name', 'last_name', 'middle_name',
        ], array_merge($common, $extra));
    }
}
