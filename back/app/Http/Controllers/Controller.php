<?php

namespace App\Http\Controllers;

use App\Http\Resources\JustAttributesResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $filters = [];
    protected $filterTablePrefix = [];
    protected $mapFilters = []; // e.g. test_client_id => tests.client_id
    protected $resources = [];

    protected function handleIndexRequest(Request $request, $query, $resource = null)
    {
        if ($request->has('count')) {
            return $query->count();
        }

        if ($request->has('with')) {
            $query->with($request->with);
        }

        $result = (clone $query)->paginate($request->paginate ?: ($request->page ? 30 : 9999));

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

    protected function filter(Request $request, &$query, array $filters = null)
    {
        $filters = $filters ?? $this->filters;
        foreach ($filters as $type => $fields) {
            foreach ($fields as $key_field => $field) {
                $f = is_array($field) ? $key_field : $field;
                if ($request->has($f)) {
                    $this->{'filter' . ucfirst($type)}($query, $request->input($f), $field);
                }
            }
        }
    }

    protected function filterEquals(&$query, $value, $field)
    {
        $query->where(DB::raw($this->getFieldName($field)), $value);
    }

    protected function filterNotNull(string $field, $value, &$query)
    {
        $query->whereNotNull($this->getFieldName($field));
    }

    protected function filterFindInSet(&$query, $values, $field)
    {
        if (is_array($values)) {
            $query->where(function ($query) use ($values, $field) {
                $query->where(function ($query) use ($values, $field) {
                    foreach ($values as $value) {
                        $query->orWhereRaw("FIND_IN_SET('{$value}', `{$field}`)");
                    }
                });
                if (in_array('null', $values)) {
                    $query->orWhereNull($field);
                }
            });
        } else {
            $query->whereRaw("FIND_IN_SET('{$values}', `{$field}`)");
        }
    }

    private function getFieldName($field)
    {
        foreach ($this->mapFilters as $originalFieldName => $mappedFieldName) {
            if ($field === $originalFieldName) {
                $field = $mappedFieldName;
            }
        }

        foreach ($this->filterTablePrefix as $table => $fields) {
            if (in_array($field, $fields)) {
                $field = "{$table}.{$field}";
            }
        }

        return "`" . $field . "`";
    }
}
