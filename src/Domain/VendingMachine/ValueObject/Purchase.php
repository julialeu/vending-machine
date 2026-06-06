<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\ValueObject;

use VendingMachine\Domain\VendingMachine\Entity\Product;

final class Purchase
{
    public function __construct(
        private readonly Product $product,
        private readonly Change $change,
    ) {
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function change(): Change
    {
        return $this->change;
    }
}
