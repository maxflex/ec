<?php

namespace App\Models;

use App\Enums\Company;
use App\Traits\HasComments;
use App\Traits\IsSearchable;
use App\Utils\Balance\Balance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasComments, IsSearchable;

    public $timestamps = false;

    protected $fillable = [
        'year', 'company', 'source',
    ];

    protected $casts = [
        'company' => Company::class,
    ];

    /**
     * @return BelongsTo<Client>
     */
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
     * @return array{to_pay: int, remainder: int, contract_payments: int, client_lessons: int}
     */
    public function getBalancesAttribute(): array
    {
        $contractPayments = $this->payments
            ->where('is_confirmed', true)
            ->reduce(fn ($carry, $p) => $carry + $p->realSum, 0);

        $clientLessons = intval($this->clientLessonsQuery()->sum('price'));

        return [
            'contract_payments' => $contractPayments,
            'client_lessons' => $clientLessons,
            'to_pay' => $this->active_version->sum - $contractPayments,
            'remainder' => $contractPayments - $clientLessons,
        ];
    }

    private function clientLessonsQuery()
    {
        return ClientLesson::whereHas(
            'contractVersionProgram.contractVersion',
            fn ($q) => $q->where('contract_id', $this->id)
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->active_version->programs->firstWhere(fn (ContractVersionProgram $p) => $p->is_active) === null;
    }

    public function getBalance(): Balance
    {
        $clientLessons = $this->clientLessonsQuery()->get();

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
                    $payment->method->getTitle(),
                ),
                $payment->is_confirmed
            );
        }

        return $balance;
    }
}
