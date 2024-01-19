<?php

namespace App\Http\Controllers;

use App\Http\Resources\JustAttributesResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $filters = [];
    protected $filterTablePrefix = [];
    protected $mapFilters = []; // e.g. test_client_id => tests.client_id
    protected $resources = [];
    protected $paginate = 30;

    protected function handleIndexRequest(Request $request, $query, $resource = null)
    {
        if ($request->has('count')) {
            return $query->count();
        }

        $result = (clone $query)->paginate($this->paginate);

        $result = ($resource ?? $this->getResource($request))::collection($result);
        if ($request->has('pluck')) {
            $result->additional([
                'ids' => $query->whereNotNull($request->pluck)->pluck($request->pluck)->values()->all(),
            ]);
        }

        return $result;
    }


    protected function getResource(Request $request)
    {
        if (count($this->resources) === 0) {
            return JustAttributesResource::class;
        }
        $resource = $this->resources['default'];
        foreach (array_keys($request->except('default')) as $key) {
            if (isset($this->resources[$key])) {
                $resource = $this->resources[$key];
            }
        }
        return $resource;
    }
}
