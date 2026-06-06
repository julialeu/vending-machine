<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\SelectProduct;

final class SelectProductResponse
{
    /** @param string[] $changeCoins */
    public function __construct(
        public readonly string $productName,
        public readonly array $changeCoins,
    ) {
    }
}
