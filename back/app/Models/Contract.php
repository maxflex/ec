<?php

namespace App\Models;

use App\Enums\Company;
use App\Traits\HasBalance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Contract extends Model
{
    use HasBalance;

    public $timestamps = false;

    protected $fillable = [
        'year', 'company', 'client_id'
    ];

    protected $casts = [
        'company' => Company::class,
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function versions()
    {
        return $this->hasMany(ContractVersion::class)->oldest();
    }

    public function groups()
    {
        return $this->hasManyThrough(
            Group::class,
            ClientGroup::class,
            'contract_id',
            'id',
            'id',
            'group_id',
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function getActiveVersion(): ContractVersion
    {
        return $this->versions()->active()->first();
    }

    protected function getBalanceItems(): array
    {
        $programIds = $this->getActiveVersion()->programs()->pluck('id');

        $clientLessons = ClientLesson::query()
            ->whereIn('contract_version_program_id', $programIds)
            ->get();

        $balanceItems = [];

        foreach ($clientLessons as $clientLesson) {
            $lesson = $clientLesson->lesson;
            $balanceItems[] = (object)[
                'dateTime' => $lesson->conducted_at,
                'sum' => $clientLesson->price * -1,
                'comment' => sprintf(
                    'занятие %s, группа %d, кабинет %s',
                    Carbon::parse($lesson->dateTime)->format('d.m.y в H:i'),
                    $lesson->group_id,
                    $lesson->cabinet->value
                )
            ];
        }

        foreach ($this->payments as $payment) {
            $balanceItems[] = [
                'dateTime' => $payment->created_at->format('Y-m-d H:i:s'),
                'sum' => $payment->sum * ($payment->is_return ? -1 : 1),
                'comment' => sprintf(
                    '%s (обучение)',
                    $payment->method->getTitle()
                )
            ];
        }

        return $balanceItems;
    }
}
