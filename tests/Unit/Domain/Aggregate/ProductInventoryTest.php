<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\Aggregate;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Aggregate\ProductInventory;
use VendingMachine\Domain\VendingMachine\Entity\Product;
use VendingMachine\Domain\VendingMachine\Exception\InvalidStockQuantityException;
use VendingMachine\Domain\VendingMachine\Exception\ProductNotAvailableException;
use VendingMachine\Domain\VendingMachine\Exception\ProductNotFoundException;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class ProductInventoryTest extends TestCase
{
    private function water(): Product
    {
        return new Product(ProductSelector::WATER, 'Water', Money::fromCents(65));
    }

    public function test_find_returns_stocked_product(): void
    {
        $inventory = new ProductInventory();
        $inventory->stock($this->water(), 3);

        $found = $inventory->find(ProductSelector::WATER);
        $this->assertSame('Water', $found->name());
    }

    public function test_find_throws_when_not_stocked(): void
    {
        $this->expectException(ProductNotFoundException::class);
        (new ProductInventory())->find(ProductSelector::WATER);
    }

    public function test_has_available_false_when_out_of_stock(): void
    {
        $inventory = new ProductInventory();
        $inventory->stock($this->water(), 0);
        $this->assertFalse($inventory->hasAvailable(ProductSelector::WATER));
    }

    public function test_has_available_true_when_in_stock(): void
    {
        $inventory = new ProductInventory();
        $inventory->stock($this->water(), 1);
        $this->assertTrue($inventory->hasAvailable(ProductSelector::WATER));
    }

    public function test_dispense_decrements_stock(): void
    {
        $inventory = new ProductInventory();
        $inventory->stock($this->water(), 2);
        $inventory->dispense(ProductSelector::WATER);
        $this->assertTrue($inventory->hasAvailable(ProductSelector::WATER));
    }

    public function test_dispense_throws_when_out_of_stock(): void
    {
        $this->expectException(ProductNotAvailableException::class);
        $inventory = new ProductInventory();
        $inventory->stock($this->water(), 0);
        $inventory->dispense(ProductSelector::WATER);
    }

    public function test_stock_throws_for_negative_quantity(): void
    {
        $this->expectException(InvalidStockQuantityException::class);
        $inventory = new ProductInventory();
        $inventory->stock($this->water(), -1);
    }

    public function test_update_quantity_changes_stock(): void
    {
        $inventory = new ProductInventory();
        $inventory->stock($this->water(), 0);
        $inventory->updateQuantity(ProductSelector::WATER, 5);
        $this->assertTrue($inventory->hasAvailable(ProductSelector::WATER));
    }

    public function test_find_available_returns_product_when_in_stock(): void
    {
        $inventory = new ProductInventory();
        $inventory->stock($this->water(), 2);

        $product = $inventory->findAvailable(ProductSelector::WATER);
        $this->assertSame('Water', $product->name());
    }

    public function test_find_available_throws_when_not_found(): void
    {
        $this->expectException(ProductNotFoundException::class);
        (new ProductInventory())->findAvailable(ProductSelector::WATER);
    }

    public function test_find_available_throws_when_out_of_stock(): void
    {
        $this->expectException(ProductNotAvailableException::class);
        $inventory = new ProductInventory();
        $inventory->stock($this->water(), 0);
        $inventory->findAvailable(ProductSelector::WATER);
    }
}
