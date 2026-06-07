<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\ValueObject\Change;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;

final class ChangeTest extends TestCase
{
    public function test_none_creates_empty_change(): void
    {
        $change = Change::none();
        $this->assertTrue($change->isEmpty());
        $this->assertEmpty($change->coins());
        $this->assertTrue($change->total()->isZero());
    }

    public function test_total_sums_all_coin_values(): void
    {
        $change = new Change(Coin::TWENTY_FIVE_CENTS, Coin::TEN_CENTS);
        $this->assertTrue($change->total()->equals(Money::fromCents(35)));
    }

    public function test_coins_returns_all_coins(): void
    {
        $change = new Change(Coin::FIVE_CENTS, Coin::TEN_CENTS);
        $this->assertCount(2, $change->coins());
    }

    public function test_is_not_empty_when_has_coins(): void
    {
        $change = new Change(Coin::FIVE_CENTS);
        $this->assertFalse($change->isEmpty());
    }
}
