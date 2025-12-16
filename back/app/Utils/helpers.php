<?php

use App\Enums\Company;
use App\Enums\ContractPaymentMethod;
use App\Enums\OtherPaymentMethod;
use App\Models\Client;
use App\Models\ContractPayment;
use App\Models\OtherPayment;
use App\Models\Representative;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

function sync_relation(Model $model, string $relation, array $requestInput): void
{
    if (! isset($requestInput[$relation])) {
        return;
    }
    $data = $requestInput[$relation];
    $actualIds = array_filter(array_column($data, 'id'), fn ($val) => ! is_null($val));
    $model->{$relation}()->whereNotIn('id', $actualIds)->each(fn ($model) => $model->delete());
    foreach ($data as $item) {
        if (isset($item['id']) && floor($item['id']) > 0) {
            $model->{$relation}()->find($item['id'])->update($item);
        } else {
            $model->{$relation}()->create($item);
        }
    }
}

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

function extract_fields_array(array|Collection $items, $fields, $merge = []): array
{
    if (! $items instanceof Collection) {
        $items = collect($items);
    }

    return $items->map(fn ($e) => extract_fields($e, $fields, $merge))->all();
}

function key_by($data, string $field, ?string $field2 = null, $value = null): array
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

function get_max_pko_number(Company $company, string $date)
{
    $year = intval(explode('-', $date)[0]);

    // платежи пробный ЕГЭ – все ООО
    $paymentsMaxPko = $company === Company::ooo ? intval(OtherPayment::query()
        ->where('method', OtherPaymentMethod::cash)
        ->where('is_return', false)
        ->whereRaw('YEAR(`date`) = ?', [$year])
        ->max('pko_number')) : 0;

    $contractPaymentsMaxPko = intval(ContractPayment::query()
        ->whereHas('contract', fn ($q) => $q->where('company', $company))
        ->where('method', ContractPaymentMethod::cash)
        ->where('is_return', false)
        ->whereRaw('YEAR(`date`) = ?', [$year])
        ->max('pko_number'));

    return max($contractPaymentsMaxPko, $paymentsMaxPko) + 1;
}

/**
 * Получить академический год по дате
 * Новый академический год начинается с 1 июля
 *
 * @param  string  $date  Дата в формате 'Y-m-d'
 * @return int Академический год
 */
function get_academic_year(string $date): int
{
    $year = (int) date('Y', strtotime($date));
    $month = (int) date('m', strtotime($date));

    return ($month >= 7) ? $year : $year - 1;
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
    return (new NumberFormatter('ru', NumberFormatter::SPELLOUT))->format($number);
}

function paginate($data, $extra = null)
{
    $result = [
        'data' => $data,
        'meta' => [
            'current_page' => 1,
            'last_page' => 1,
            'total' => count($data),
        ],
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

/**
 * Teacher => teachers
 * ClientParent => parents
 * Client => students
 */
function get_entity_type_key(string $entityType): string
{
    return match ($entityType) {
        Teacher::class => 'teachers',
        Client::class => 'students',
        Representative::class => 'representatives',
    };
}

function enum_value(BackedEnum|UnitEnum|string $enum): string
{
    return match (true) {
        $enum instanceof BackedEnum => $enum->value,
        $enum instanceof UnitEnum => $enum->name,
        default => $enum,
    };
}

function plural($n, $one, $few, $many)
{
    $text = $n % 10 == 1 && $n % 100 != 11 ? $one : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? $few : $many);

    return implode(' ', [$n, $text]);
}

function date_range(string $start, string $end): Generator
{
    $current = new DateTimeImmutable($start);
    $end = new DateTimeImmutable($end);

    while ($current <= $end) {
        yield $current;
        $current = $current->modify('+1 day');
    }
}

// private function sanitizeForTsv(string $text): string
// {
//     return preg_replace('/[\t\r\n]+/', ' ', trim($text));
// }
//

function save_csv(array|Collection $csv): string
{
    $str = '';
    foreach ($csv as $row) {
        $str .= implode("\t", $row)."\n";
    }
    $filename = uniqid().'.tsv';
    Storage::put("crm/other/$filename", $str);

    return cdn('other', $filename);
}

// доля
function share(int $numerator, int $denominator, bool $multiplyBy100 = false): float
{
    if ($denominator === 0) {
        return 0;
    }

    return round($numerator / $denominator * ($multiplyBy100 ? 100 : 1), 1);
}
