<?php

namespace App\Traits;

trait HasBalance
{
    public function getBalance(?int $year = null): array
    {
        $balanceItemsGrouped = collect($this->getBalanceItems($year))
            ->sort(fn($a, $b) => $a->dateTime <=> $b->dateTime)
            ->groupBy(fn($item) => str($item->dateTime)->before(' ')->value());

        $data = [];
        $balance = 0;

        foreach ($balanceItemsGrouped as $date => $items) {
            $balance += $items->sum(fn($e) => $e->sum);
            $data[] = (object)[
                'date' => $date,
                'balance' => $balance,
                'items' => $items->map(fn($e) => (object)[
                    'sum' => $e->sum,
                    'comment' => $e->comment,
                ])->all()
            ];
        }

        return array_reverse($data);
    }
}