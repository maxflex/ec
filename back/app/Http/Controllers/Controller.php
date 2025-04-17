<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $filters = [];

    protected $filterTablePrefix = [];

    protected $mapFilters = []; // e.g. test_client_id => tests.client_id

    protected function handleIndexRequest(Request $request, $query, $resource = null)
    {
        $resource = $resource ?? JsonResource::class;

        if ($request->has('available_years')) {
            return $this->getAvailableYears($query->distinct()->reorder())->unique()->sortDesc()->values()->all();
        }

        if ($request->has('count')) {
            return $query->count();
        }

        if ($request->has('with')) {
            $query->with($request->with);
        }

        if (! $request->has('page')) {
            return paginate($resource::collection($query->get()));
        }

        $result = (clone $query)->paginate($request->paginate ?? 30);
        $result = $resource::collection($result);

        if ($request->has('pluck')) {
            $value = $request->input('pluck');
            $result->additional([
                'ids' => $query->whereNotNull($value)->pluck($value)->values()->all(),
            ]);
        }

        if ($request->has('unique') && intval($request->page) === 1) {
            $value = $request->input('unique');
            $result->additional([
                'extra' => [
                    $value => $query->whereNotNull($value)->pluck($value)->unique()->values()->all(),
                ],
            ]);
        }

        return $result;
    }

    protected function getAvailableYears($query): Collection
    {
        return $query->pluck('year');
    }

    protected function filter(Request $request, $query, ?array $filters = null)
    {
        $filters = $filters ?? $this->filters;
        foreach ($filters as $type => $fields) {
            foreach ($fields as $key_field => $field) {
                $f = is_array($field) ? $key_field : $field;
                if ($request->has($f)) {
                    $this->{'filter'.ucfirst($type)}($query, $request->input($f), $field);
                }
            }
        }
    }

    protected function filterEquals($query, $value, $field)
    {
        $query->where(DB::raw($this->getFieldName($field)), $value);
    }

    // protected function filterNotNull(string $field, $value, $query)
    // {
    //     $query->whereNotNull($this->getFieldName($field));
    // }

    protected function getFieldName($field)
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

        return $field;
    }

    protected function filterNull($query, $value, $field)
    {
        $field = $this->getFieldName($field);
        $value ? $query->whereNotNull($field) : $query->whereNull($field);
    }

    protected function filterFindInSet($query, $values, $field)
    {
        $field = $this->getFieldName($field);
        if (is_array($values)) {
            if (count($values) === 0) {
                return;
            }
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

    protected function filterOrder($query, $value)
    {
        $order = is_array($value) ? (object) $value : json_decode($value);
        $query->orderBy($this->getFieldName($order->field), $order->order);
    }

    protected function filterHas($query, $value, $field)
    {
        $relation = str($field)->after('has_')->camel()->value();
        $value ? $query->whereHas($relation) : $query->whereDoesntHave($relation);
    }

    protected function filterGte($query, $value, $field)
    {
        if ($value) {
            $query->where(DB::raw($this->getFieldName($field)), '>=', $value);
        }
    }

    protected function filterSearchByName($query, $value)
    {
        $words = explode(' ', $value);

        foreach ($words as $word) {
            $query->whereAny(
                ['first_name', 'last_name', 'middle_name'],
                'like',
                "{$word}%"
            );
        }
    }

    protected function filterLte($query, $value, $field)
    {
        if ($value) {
            $query->where(DB::raw($this->getFieldName($field)), '<=', $value);
        }
    }
}
