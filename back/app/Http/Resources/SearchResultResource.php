<?php

namespace App\Http\Resources;

use App\Models\Client;
use App\Models\ClientParent;
use App\Models\Contract;
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
            'phones' => $model->phones ? PhoneResource::collection($model->phones) : [],
        ];

        switch ($class) {
            case Contract::class:
                $extra = [
                    'contract' => extract_fields($model, [
                        'year', 'company', 'client_id',
                    ], [
                        'programs_count' => $model->activeVersion->programs()->count(),
                        'directions' => $model->activeVersion->directions,
                    ]),
                ];
                break;

            case ClientParent::class:
                $client = $model->client;

            case Client::class:
                $client = $client ?? $model;
                $extra = [
                    'client' => [
                        'id' => $client->id,
                        'directions' => $client->directions,
                        'max_contract_year' => $client->contracts->max('year'),
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
                $model->loadCount('comments');
                $extra = [
                    'request' => new RequestListResource($model),
                ];
        }

        return extract_fields($model, [
            'first_name', 'last_name', 'middle_name',
        ], array_merge($common, $extra));
    }
}
