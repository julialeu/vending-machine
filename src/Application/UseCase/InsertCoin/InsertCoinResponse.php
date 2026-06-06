<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\InsertCoin;

final class InsertCoinResponse
{
    public function __construct(
        public readonly string $totalInserted,
    ) {
    }
}
