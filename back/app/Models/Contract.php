<?php

namespace App\Models;

use App\Enums\Company;
use App\Traits\HasBalance;
use App\Traits\IsSearchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use HasBalance, IsSearchable;

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

    public function payments(): HasMany
    {
        return $this->hasMany(ContractPayment::class);
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

    protected function getBalanceItems(): array
    {
        $programIds = $this->active_version->programs()->pluck('id');

        $clientLessons = ClientLesson::query()
            ->whereIn('contract_version_program_id', $programIds)
            ->get();

        $balanceItems = [];

        foreach ($clientLessons as $clientLesson) {
            $lesson = $clientLesson->lesson;
            $balanceItems[] = (object) [
                'dateTime' => $lesson->conducted_at,
                'sum' => $clientLesson->price * -1,
                'comment' => sprintf(
                    'занятие %s, группа %d, кабинет %s',
                    $lesson->date_time->format('d.m.y в H:i'),
                    $lesson->group_id,
                    filter_var($lesson->cabinet->value, FILTER_SANITIZE_NUMBER_INT)
                ),
            ];
        }

        foreach ($this->payments as $payment) {
            $balanceItems[] = (object) [
                'dateTime' => $payment->created_at->format('Y-m-d H:i:s'),
                'sum' => $payment->sum * ($payment->is_return ? -1 : 1),
                'comment' => sprintf(
                    '%s (обучение)',
                    $payment->method->getTitle()
                ),
            ];
        }

        return $balanceItems;
    }
}
