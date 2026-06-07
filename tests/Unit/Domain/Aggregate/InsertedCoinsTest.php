<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\Aggregate;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Aggregate\InsertedCoins;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;

final class InsertedCoinsTest extends TestCase
{
    public function test_starts_empty(): void
    {
        $inserted = new InsertedCoins();
        $this->assertTrue($inserted->isEmpty());
        $this->assertTrue($inserted->total()->isZero());
    }

    public function test_insert_accumulates_total(): void
    {
        $inserted = new InsertedCoins();
        $inserted->insert(Coin::TWENTY_FIVE_CENTS);
        $inserted->insert(Coin::TEN_CENTS);
        $this->assertTrue($inserted->total()->equals(Money::fromCents(35)));
    }

    public function test_release_all_empties_and_returns_coins(): void
    {
        $inserted = new InsertedCoins();
        $inserted->insert(Coin::FIVE_CENTS);
        $inserted->insert(Coin::TWENTY_FIVE_CENTS);

        $coins = $inserted->releaseAll();

        $this->assertCount(2, $coins);
        $this->assertTrue($inserted->isEmpty());
    }

    public function test_coins_returns_without_emptying(): void
    {
        $inserted = new InsertedCoins();
        $inserted->insert(Coin::TEN_CENTS);

        $inserted->coins();

        $this->assertFalse($inserted->isEmpty());
    }
}
