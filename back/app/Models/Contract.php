<?php

namespace App\Models;

use App\Enums\Company;
use App\Traits\IsSearchable;
use App\Utils\Balance\Balance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use IsSearchable;

    public $timestamps = false;

    protected $fillable = [
        'year', 'company', 'client_id',
    ];

    protected $casts = [
        'company' => Company::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getActiveVersionAttribute(): ContractVersion
    {
        return $this->versions->where('is_active', true)->first();
    }

    public function getFirstVersionAttribute(): ContractVersion
    {
        return $this->versions()->first();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ContractVersion::class)->oldest();
    }

    public function getSearchWeight(): int
    {
        return 150;
    }

    public function getSearchPhones(): array
    {
        return [
            (string) $this->id,
        ];
    }

    public function getSearchIsActive(): bool
    {
        return $this->year >= current_academic_year();
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->where('year', '>=', current_academic_year() - 3);
    }

    /**
     * @return ?object{toPay: int, remainder: int}
     */
    public function getBalancesAttribute(): ?object
    {
        $payments = $this->payments()->get();

        if ($payments->isEmpty()) {
            return null;
        }

        $paid = 0;

        foreach ($this->payments as $payment) {
            $paid += $payment->sum * ($payment->is_return ? -1 : 1);
        }

        return (object) [
            'toPay' => $this->active_version->sum - $paid,
            'remainder' => $this->getBalance()->getCurrent(),
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function getBalance(): Balance
    {
        $clientLessons = ClientLesson::whereHas(
            'contractVersionProgram.contractVersion',
            fn ($q) => $q->where('contract_id', $this->id)
        )->get();

        $balance = new Balance;

        foreach ($clientLessons as $clientLesson) {
            $lesson = $clientLesson->lesson;
            $balance->push(
                $clientLesson->price * -1,
                $lesson->conducted_at,
                sprintf(
                    'занятие %s, группа %d, кабинет %s',
                    $lesson->date_time->format('d.m.y в H:i'),
                    $lesson->group_id,
                    filter_var($lesson->cabinet->value, FILTER_SANITIZE_NUMBER_INT)
                )
            );
        }

        foreach ($this->payments as $payment) {
            $balance->push(
                $payment->sum * ($payment->is_return ? -1 : 1),
                $payment->created_at->format('Y-m-d H:i:s'),
                sprintf(
                    '%s (обучение)',
                    $payment->method->getTitle()
                ),
            );
        }

        return $balance;
    }
}
