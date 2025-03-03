<?php

namespace App\Utils\Balance;

readonly class BalanceItem
{
    public function __construct(
        public int $sum,
        public string $dateTime,
        public string $comment
    ) {}
}
