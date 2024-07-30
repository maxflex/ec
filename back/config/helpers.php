<?php

use App\Enums\ClientPaymentMethod;
use App\Enums\Company;
use App\Models\ClientPayment;
use App\Models\ContractPayment;
use Illuminate\Support\Collection;

function extract_fields($object, $fields, $merge = []): ?array
{
    if ($object === null) {
        return null;
    }
    $return = ['id' => $object->id];
    foreach ($fields as $field) {
        if ($field === '*') {
            $object = $object->resource;
            if (method_exists($object, 'toArray')) {
                $object = $object->toArray();
            }
            foreach ($object as $key => $value) {
                if ($key === 'id') {
                    continue;
                }
                $return[$key] = $value;
            }
        } else {
            $return[$field] = $object->{$field};
        }
    }
    return array_merge($return, $merge);
}

function extract_fields_array(array | Collection $items, $fields, $merge = []): array
{
    if (!$items instanceof Collection) {
        $items = collect($items);
    }
    return $items->map(fn ($e) => extract_fields($e, $fields, $merge))->all();
}

function key_by($data, string $field, string $field2 = null, $value = null): array
{
    $result = [];
    if ($field2 === null) {
        foreach ($data as $d) {
            $key = $d->{$field};
            if ($value === null) {
                unset($d->{$field});
                $result[$key] = $d;
            } else {
                $result[$key] = $d->$value;
            }
        }
    } else {
        foreach ($data as $d) {
            $key = $d->{$field};
            $key2 = $d->{$field2};
            if ($value === null) {
                unset($d->{$field});
                unset($d->{$field2});
                @$result[$key][$key2] = $d;
            } else {
                @$result[$key][$key2] = $d->$value;
            }
        }
    }
    return $result;
}

function cdn(string $folder, string $file)
{
    return "https://cdn.ege-centr.ru/{$folder}/{$file}";
}

function is_localhost()
{
    return app()->environment('local');
}

function get_max_pko_number(Company $company)
{
    return max(
        ClientPayment::query()
            ->where('company', $company)
            ->where('method', ClientPaymentMethod::cash)
            ->max('pko_number'),
        ContractPayment::query()
            ->whereHas('contract', fn ($q) => $q->where('company', $company))
            ->where('method', ClientPaymentMethod::cash)
            ->max('pko_number'),
    ) + 1;
}
