<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\InsertCoin;

final class InsertCoinRequest
{
    public function __construct(
        public readonly int $coinCents,
    ) {
    }
}
