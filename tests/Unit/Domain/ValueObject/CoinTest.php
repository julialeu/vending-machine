<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Exception\InvalidCoinException;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;

final class CoinTest extends TestCase
{
    public function test_from_cents_returns_correct_coin(): void
    {
        $this->assertSame(Coin::FIVE_CENTS, Coin::fromCents(5));
        $this->assertSame(Coin::TEN_CENTS, Coin::fromCents(10));
        $this->assertSame(Coin::TWENTY_FIVE_CENTS, Coin::fromCents(25));
        $this->assertSame(Coin::ONE_EURO, Coin::fromCents(100));
    }

    public function test_from_cents_throws_for_invalid_denomination(): void
    {
        $this->expectException(InvalidCoinException::class);
        Coin::fromCents(15);
    }

    public function test_to_money_returns_correct_amount(): void
    {
        $this->assertTrue(Coin::TWENTY_FIVE_CENTS->toMoney()->equals(Money::fromCents(25)));
        $this->assertTrue(Coin::ONE_EURO->toMoney()->equals(Money::fromCents(100)));
    }

    public function test_small_coins_are_returnable_as_change(): void
    {
        $this->assertTrue(Coin::FIVE_CENTS->isReturnableAsChange());
        $this->assertTrue(Coin::TEN_CENTS->isReturnableAsChange());
        $this->assertTrue(Coin::TWENTY_FIVE_CENTS->isReturnableAsChange());
    }

    public function test_one_euro_is_not_returnable_as_change(): void
    {
        $this->assertFalse(Coin::ONE_EURO->isReturnableAsChange());
    }

    public function test_display_value_formats_as_decimal(): void
    {
        $this->assertSame('0.05', Coin::FIVE_CENTS->displayValue());
        $this->assertSame('0.10', Coin::TEN_CENTS->displayValue());
        $this->assertSame('0.25', Coin::TWENTY_FIVE_CENTS->displayValue());
        $this->assertSame('1.00', Coin::ONE_EURO->displayValue());
    }
}
