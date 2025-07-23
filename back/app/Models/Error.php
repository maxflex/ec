<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Error extends Model
{
    const DISABLE_LOGS = true;

    public $timestamps = false;

    protected $fillable = ['entity_id', 'entity_type', 'code'];

    public static function check(): void
    {
        // clear out old errors
        DB::table('errors')->truncate();

        self::error1000();
        self::error2000();
    }

    private static function error1000(): void
    {
        $ids = Phone::query()
            ->whereRaw('LENGTH(number) <> 11')
            ->pluck('id');

        self::createErrors(1000, $ids, Phone::class);
    }

    private static function createErrors(int $code, Collection $ids, string $entityType): void
    {
        $ids->unique()->each(fn ($id) => self::create([
            'code' => $code,
            'entity_id' => $id,
            'entity_type' => $entityType,
        ]));
    }

    private static function error2000(): void
    {
        Contract::query()
            ->where('year', 2024)
            ->with(['versions' => fn ($q) => $q
                // only eager-load the active version and its nested relations
                ->where('is_active', true)
                ->with(['programs.prices', 'payments']),
            ])
            ->chunk(100, function (Collection $contracts) {
                $ids = collect();

                foreach ($contracts as $contract) {
                    $version = $contract->active_version;

                    if (! self::isValidVersion($version)) {
                        $ids->push($contract->id);
                    }
                }

                if ($ids->isNotEmpty()) {
                    // write a 2000-error for each bad contract
                    self::createErrors(2000, $ids, Contract::class);
                }
            });
    }

    private static function isValidVersion(ContractVersion $v): bool
    {
        return self::isPaymentSumValid($v)
            && self::isLessonsValid($v)
            && self::isPriceValid($v);
    }

    private static function isPaymentSumValid(ContractVersion $v): bool
    {
        $paymentSum = $v->payments->sum(fn ($p) => (int) $p->sum);
        $contractSum = (int) $v->sum;
        $programSum = $v->programs
            ->flatMap->prices
            ->sum(fn ($pr) => (int) $pr->lessons * (int) $pr->price);

        if ($paymentSum === 0) {
            return true;
        }

        return $contractSum > 0
            && $contractSum === $programSum
            && $programSum === $paymentSum;
    }

    private static function isLessonsValid(ContractVersion $v): bool
    {
        foreach ($v->programs as $p) {
            $total = (int) $p->getLessonsSuggest($p->group);
            $sum = $p->prices->sum(fn ($pr) => (int) $pr->lessons);

            if ($total > 0 && $total !== $sum) {
                return false;
            }
        }

        return true;
    }

    private static function isPriceValid(ContractVersion $v): bool
    {
        foreach ($v->programs as $p) {
            $offset = 0;
            $history = $p->clientLessonPrices->all();

            foreach ($p->prices as $pr) {
                $count = (int) $pr->lessons;
                $slice = array_slice($history, $offset, $count);

                if (! empty($slice)) {
                    $unique = collect($slice)->unique()->values();
                    if ($unique->count() !== 1
                        || $unique->first() !== (int) $pr->price
                    ) {
                        return false;
                    }
                }

                $offset += $count;
            }
        }

        return true;
    }

    public function entity()
    {
        return $this->morphTo();
    }
}
