<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\AddProduct;

final class AddProductRequest
{
    public function __construct(
        public readonly string $selectorValue,
        public readonly string $name,
        public readonly int $priceCents,
        public readonly int $quantity,
    ) {
    }
}
