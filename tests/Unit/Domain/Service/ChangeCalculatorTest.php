<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\Service;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Aggregate\CoinInventory;
use VendingMachine\Domain\VendingMachine\Exception\InsufficientChangeException;
use VendingMachine\Domain\VendingMachine\Service\ChangeCalculator;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;

final class ChangeCalculatorTest extends TestCase
{
    private ChangeCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new ChangeCalculator();
    }

    private function inventoryWith(int $fives, int $tens, int $twentyFives): CoinInventory
    {
        $inventory = new CoinInventory();
        $inventory->set(Coin::FIVE_CENTS, $fives);
        $inventory->set(Coin::TEN_CENTS, $tens);
        $inventory->set(Coin::TWENTY_FIVE_CENTS, $twentyFives);

        return $inventory;
    }

    public function test_calculates_change_with_single_coin(): void
    {
        $inventory = $this->inventoryWith(0, 1, 0);
        $change = $this->calculator->calculate(Money::fromCents(10), $inventory);
        $this->assertSame(10, $change->total()->cents());
        $this->assertCount(1, $change->coins());
    }

    public function test_uses_largest_denomination_first(): void
    {
        $inventory = $this->inventoryWith(10, 10, 10);
        $change = $this->calculator->calculate(Money::fromCents(35), $inventory);
        // greedy: 25 + 10 = 35 (2 coins, not 7x5)
        $this->assertSame(35, $change->total()->cents());
        $this->assertCount(2, $change->coins());
    }

    public function test_uses_multiple_small_coins_when_needed(): void
    {
        $inventory = $this->inventoryWith(4, 0, 0);
        $change = $this->calculator->calculate(Money::fromCents(20), $inventory);
        $this->assertSame(20, $change->total()->cents());
        $this->assertCount(4, $change->coins());
    }

    public function test_throws_when_no_coins_available(): void
    {
        $this->expectException(InsufficientChangeException::class);
        $inventory = $this->inventoryWith(0, 0, 0);
        $this->calculator->calculate(Money::fromCents(10), $inventory);
    }

    public function test_one_euro_coin_is_never_used_for_change(): void
    {
        $this->expectException(InsufficientChangeException::class);
        $inventory = new CoinInventory();
        $inventory->set(Coin::ONE_EURO, 10);
        $this->calculator->calculate(Money::fromCents(100), $inventory);
    }
}
