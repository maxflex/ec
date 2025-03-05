<?php

namespace App\Utils\Balance;

use App\Models\Teacher;
use Illuminate\Support\Collection;

class Balance
{
    /** @var Collection<int, BalanceItem> */
    protected Collection $items;

    public function __construct()
    {
        $this->items = collect();
    }

    public static function getAvailableYears(Teacher $teacher): array
    {
        return $teacher->lessons()
            ->join('groups', 'groups.id', '=', 'lessons.group_id')
            ->conducted()
            ->distinct()
            ->pluck('groups.year')
            ->unique()
            ->sortDesc()
            ->values()
            ->all();
    }

    public function push(int $sum, string $dateTime, string $comment)
    {
        $this->items->push(
            new BalanceItem($sum, $dateTime, $comment),
        );
    }

    /**
     * Текущий баланс
     */
    public function getCurrent(): int
    {
        return $this->groupByDay()[0]?->balance;
    }

    public function groupByDay(): array
    {
        $itemsGrouped = $this->items
            ->sort(fn (BalanceItem $a, BalanceItem $b) => $a->dateTime <=> $b->dateTime)
            ->groupBy(fn (BalanceItem $item) => str($item->dateTime)->before(' ')->value());

        $data = [];
        $balance = 0;

        foreach ($itemsGrouped as $date => $items) {
            $balance += $items->sum(fn (BalanceItem $e) => $e->sum);
            $data[] = (object) [
                'date' => $date,
                'balance' => $balance,
                'items' => $items->map(fn (BalanceItem $e) => (object) [
                    'sum' => $e->sum,
                    'comment' => $e->comment,
                ])->all(),
            ];
        }

        return array_reverse($data);
    }
}
