<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\ReturnCoins;

final class ReturnCoinsResponse
{
    /** @param string[] $returnedCoins */
    public function __construct(
        public readonly array $returnedCoins,
    ) {
    }

    public function isEmpty(): bool
    {
        return empty($this->returnedCoins);
    }
}
