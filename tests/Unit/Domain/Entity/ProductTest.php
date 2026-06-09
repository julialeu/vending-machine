<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Entity\Product;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class ProductTest extends TestCase
{
    public function test_exposes_selector(): void
    {
        $product = new Product(ProductSelector::WATER, 'Water', Money::fromCents(65));
        $this->assertSame(ProductSelector::WATER, $product->selector());
    }

    public function test_exposes_name(): void
    {
        $product = new Product(ProductSelector::JUICE, 'Juice', Money::fromCents(100));
        $this->assertSame('Juice', $product->name());
    }

    public function test_exposes_price(): void
    {
        $product = new Product(ProductSelector::SODA, 'Soda', Money::fromCents(150));
        $this->assertTrue($product->price()->equals(Money::fromCents(150)));
    }
}
