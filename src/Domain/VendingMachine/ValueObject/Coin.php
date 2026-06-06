<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\ValueObject;

use VendingMachine\Domain\VendingMachine\Exception\InvalidCoinException;

enum Coin: int
{
    case FIVE_CENTS = 5;
    case TEN_CENTS = 10;
    case TWENTY_FIVE_CENTS = 25;
    case ONE_EURO = 100;

    public static function fromCents(int $cents): self
    {
        return self::tryFrom($cents) ?? throw new InvalidCoinException($cents);
    }

    public function toMoney(): Money
    {
        return Money::fromCents($this->value);
    }

    /** The machine only returns 0.05, 0.10 and 0.25 as change — 1.00 is accepted but never dispensed */
    public function isReturnableAsChange(): bool
    {
        return $this !== self::ONE_EURO;
    }

    public function displayValue(): string
    {
        return number_format($this->value / 100, 2);
    }
}
