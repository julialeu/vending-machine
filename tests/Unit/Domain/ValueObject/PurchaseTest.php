<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Entity\Product;
use VendingMachine\Domain\VendingMachine\ValueObject\Change;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;
use VendingMachine\Domain\VendingMachine\ValueObject\Purchase;

final class PurchaseTest extends TestCase
{
    public function test_exposes_product(): void
    {
        $product = new Product(ProductSelector::WATER, 'Water', Money::fromCents(65));
        $purchase = new Purchase($product, Change::none());

        $this->assertSame($product, $purchase->product());
    }

    public function test_exposes_change(): void
    {
        $product = new Product(ProductSelector::WATER, 'Water', Money::fromCents(65));
        $change = new Change(Coin::TEN_CENTS);
        $purchase = new Purchase($product, $change);

        $this->assertSame($change, $purchase->change());
    }

    public function test_exposes_empty_change(): void
    {
        $purchase = new Purchase(
            new Product(ProductSelector::JUICE, 'Juice', Money::fromCents(100)),
            Change::none(),
        );

        $this->assertTrue($purchase->change()->isEmpty());
    }
}
