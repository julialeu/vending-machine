<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Entity;

use VendingMachine\Domain\VendingMachine\ValueObject\Money;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class Product
{
    public function __construct(
        private readonly ProductSelector $selector,
        private readonly string $name,
        private readonly Money $price,
    ) {
    }

    public function selector(): ProductSelector
    {
        return $this->selector;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }


}
