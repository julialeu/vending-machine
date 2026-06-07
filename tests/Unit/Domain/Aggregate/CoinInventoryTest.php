<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\Aggregate;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Aggregate\CoinInventory;
use VendingMachine\Domain\VendingMachine\Exception\InvalidStockQuantityException;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;

final class CoinInventoryTest extends TestCase
{
    public function test_starts_with_zero_for_any_coin(): void
    {
        $inventory = new CoinInventory();
        $this->assertSame(0, $inventory->countOf(Coin::FIVE_CENTS));
    }

    public function test_add_increments_count(): void
    {
        $inventory = new CoinInventory();
        $inventory->add(Coin::TEN_CENTS);
        $inventory->add(Coin::TEN_CENTS);
        $this->assertSame(2, $inventory->countOf(Coin::TEN_CENTS));
    }

    public function test_remove_decrements_count(): void
    {
        $inventory = new CoinInventory();
        $inventory->set(Coin::TWENTY_FIVE_CENTS, 3);
        $inventory->remove(Coin::TWENTY_FIVE_CENTS);
        $this->assertSame(2, $inventory->countOf(Coin::TWENTY_FIVE_CENTS));
    }

    public function test_remove_does_not_go_below_zero(): void
    {
        $inventory = new CoinInventory();
        $inventory->remove(Coin::FIVE_CENTS);
        $this->assertSame(0, $inventory->countOf(Coin::FIVE_CENTS));
    }

    public function test_set_defines_exact_quantity(): void
    {
        $inventory = new CoinInventory();
        $inventory->set(Coin::ONE_EURO, 5);
        $this->assertSame(5, $inventory->countOf(Coin::ONE_EURO));
    }

    public function test_set_throws_for_negative_quantity(): void
    {
        $this->expectException(InvalidStockQuantityException::class);
        $inventory = new CoinInventory();
        $inventory->set(Coin::FIVE_CENTS, -1);
    }
}
