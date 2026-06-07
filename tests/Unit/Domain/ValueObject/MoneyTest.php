<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Exception\InvalidMoneyAmountException;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;

final class MoneyTest extends TestCase
{
    public function test_creates_from_cents(): void
    {
        $money = Money::fromCents(25);
        $this->assertSame(25, $money->cents());
    }

    public function test_zero_has_zero_cents(): void
    {
        $money = Money::zero();
        $this->assertTrue($money->isZero());
        $this->assertSame(0, $money->cents());
    }

    public function test_throws_for_negative_cents(): void
    {
        $this->expectException(InvalidMoneyAmountException::class);
        Money::fromCents(-1);
    }

    public function test_add_returns_sum(): void
    {
        $result = Money::fromCents(25)->add(Money::fromCents(10));
        $this->assertSame(35, $result->cents());
    }

    public function test_subtract_returns_difference(): void
    {
        $result = Money::fromCents(75)->subtract(Money::fromCents(65));
        $this->assertSame(10, $result->cents());
    }

    public function test_subtract_throws_when_result_would_be_negative(): void
    {
        $this->expectException(InvalidMoneyAmountException::class);
        Money::fromCents(10)->subtract(Money::fromCents(25));
    }

    public function test_is_greater_than_or_equal_to(): void
    {
        $this->assertTrue(Money::fromCents(75)->isGreaterThanOrEqualTo(Money::fromCents(65)));
        $this->assertTrue(Money::fromCents(65)->isGreaterThanOrEqualTo(Money::fromCents(65)));
        $this->assertFalse(Money::fromCents(10)->isGreaterThanOrEqualTo(Money::fromCents(65)));
    }

    public function test_equals(): void
    {
        $this->assertTrue(Money::fromCents(25)->equals(Money::fromCents(25)));
        $this->assertFalse(Money::fromCents(25)->equals(Money::fromCents(10)));
    }

    public function test_to_string_formats_as_decimal(): void
    {
        $this->assertSame('0.25', (string) Money::fromCents(25));
        $this->assertSame('1.00', (string) Money::fromCents(100));
        $this->assertSame('0.05', (string) Money::fromCents(5));
    }
}
