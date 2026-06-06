<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\SelectProduct;

final class SelectProductRequest
{
    public function __construct(
        public readonly string $productSelector,
    ) {
    }
}
