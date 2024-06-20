<?php

function extract_fields($object, $fields, $merge = []): ?array
{
    if ($object === null) {
        return null;
    }
    $return = ['id' => $object->id];
    foreach ($fields as $field) {
        if ($field === '*') {
            foreach ($object->resource->toArray() as $key => $value) {
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
