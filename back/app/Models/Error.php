<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class Error extends Model
{
    const DISABLE_LOGS = true;
    public $timestamps = false;

    protected $fillable = [
        'entity_id', 'entity_type', 'code'
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public static function check()
    {
        DB::table('errors')->truncate();
        self::checkPhones();
        self::checkContracts();
    }

    private static function checkPhones()
    {
        $ids = Phone::query()
            ->whereRaw('length(number) <> 11')
            ->pluck('id');

        self::createErrors($ids, Phone::class);
    }

    private static function createErrors(Collection $ids, string $entityType): void
    {
        $ids->each(fn($id) => Error::create([
            'code' => 1000,
            'entity_id' => $id,
            'entity_type' => $entityType
        ]));
    }

    private static function checkContracts(): void
    {
        $contracts = Contract::query()
            ->where('year', 2024)
            ->with(['versions.programs.prices', 'versions.payments'])
            ->get();

        $ids = collect();

        foreach ($contracts as $contract) {
            foreach ($contract->versions as $version) {
                if (self::hasContractErrors($version)) {
                    $ids->push($contract->id);
                    break;
                }
            }
        }

        $ids->unique()->each(fn($id) => Error::create([
            'code' => 2000,
            'entity_id' => $id,
            'entity_type' => Contract::class,
        ]));
    }

    private static function hasContractErrors(ContractVersion $version): bool
    {
        $paymentSum = $version->payments->sum('sum');

        $lessonsPriceSum = $version->programs->reduce(function ($carry, $program) {
            return $carry + $program->prices->sum(fn($p) => $p->lessons * $p->price);
        }, 0);

        $contractSum = (int) $version->sum;

        $isPaymentSumValid = ! $paymentSum || (
            $contractSum > 0
            && $contractSum === $lessonsPriceSum
            && $lessonsPriceSum === $paymentSum
        );

        if (! $isPaymentSumValid) {
            return true;
        }

        foreach ($version->programs as $program) {
            foreach ($program->prices as $price) {
                if (self::isPriceLessonsError($price, $program) || self::isPriceError($price, $program)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function isPriceLessonsError(ContractVersionProgramPrice $price, ContractVersionProgram $program): bool
    {
        if ((!$program->group_id && !$program->lessons_conducted) || $program->prices->contains(fn($p) => $p->lessons === null)) {
            return false;
        }

        $lessonsSum = $program->prices->sum('lessons');

        return $price->id === $program->prices->last()->id && $lessonsSum !== $program->lessons_total;
    }

    private static function isPriceError(ContractVersionProgramPrice $price, ContractVersionProgram $program): bool
    {
        $skip = 0;
        $clientPrices = $program->client_lesson_prices->all();

        foreach ($program->prices as $p) {
            $lessons = (int) $p->lessons;
            $currentPrices = array_values(array_unique(array_slice($clientPrices, $skip, $lessons)));

            if (count($currentPrices) === 0) {
                return false;
            }

            if ($p->id === $price->id) {
                $isCorrectPrice = count($currentPrices) === 1 && $currentPrices[0] === (int) $price->price;
                return ! $isCorrectPrice;
            }

            $skip += $lessons;
        }

        return false;
    }

}
