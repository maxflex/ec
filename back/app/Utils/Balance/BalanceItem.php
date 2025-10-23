<?php

namespace App\Utils\Balance;

readonly class BalanceItem
{
    /**
     * @param  bool  $isConfirmed  если не подтвержден, то отображается серым и не влияет на баланс (в обработке)
     */
    public function __construct(
        public int $sum,
        public string $dateTime,
        public string $comment,
        public bool $isConfirmed = true,
    ) {}
}
