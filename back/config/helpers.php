<?php

use App\Enums\Company;
use App\Enums\ContractPaymentMethod;
use App\Models\ClientPayment;
use App\Models\ContractPayment;
use App\Models\User;
use Illuminate\Support\Collection;

function extract_fields($object, $fields, $merge = []): ?array
{
    if ($object === null) {
        return null;
    }
    $return = isset($object->id) ? ['id' => $object->id] : [];
    foreach ($fields as $field) {
        // звёздочка должна быть последним элементом массива
        if ($field === '*') {
            $object = $object->resource ?? $object;
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
    return "https://cdn.ege-centr.ru/crm/$folder/$file";
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
            ->where('method', ContractPaymentMethod::cash)
            ->max('pko_number'),
        ContractPayment::query()
            ->whereHas('contract', fn ($q) => $q->where('company', $company))
            ->where('method', ContractPaymentMethod::cash)
            ->max('pko_number'),
    ) + 1;
}

/**
 * Получить академический год по дате
 * Новый академический год начинается с 1 сентября
 *
 * @param string $date Дата в формате 'Y-m-d'
 * @return int Академический год
 */
function get_academic_year(string $date): int
{
    $year = (int)date('Y', strtotime($date));
    $month = (int)date('m', strtotime($date));
    return ($month >= 9) ? $year : $year - 1;
}

/**
 * Текущий академический год
 *
 * @return int Текущий академический год
 */
function current_academic_year(): int
{
    return get_academic_year(date('Y-m-d'));
}

function json_redecode($object, $assoc = true)
{
    return json_decode(json_encode($object), $assoc);
}

function format_date(string $date, string $format = 'd.m.Y'): string
{
    return date($format, strtotime($date));
}

function num_to_text(int $number)
{
    return (new NumberFormatter("ru", NumberFormatter::SPELLOUT))->format($number);
}

function paginate($data, $extra = null)
{
    $result = [
        'data' => $data,
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'total' => count($data)
        ]
    ];

    if ($extra) {
        $result['extra'] = $extra;
    }

    return $result;
}

function get_preview_user(): ?User
{
    if (request()->hasHeader('Preview')) {
        $user = \App\Utils\Session::get(request()->header('Preview'));
        return $user instanceof User ? $user : null;
    }
    return null;
}