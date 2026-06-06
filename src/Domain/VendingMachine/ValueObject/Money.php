<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\ValueObject;

use VendingMachine\Domain\VendingMachine\Exception\InvalidMoneyAmountException;

final class Money
{
    private function __construct(private readonly int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        if ($cents < 0) {
            throw new InvalidMoneyAmountException($cents);
        }

        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(self $other): self
    {
        if ($other->cents > $this->cents) {
            throw new InvalidMoneyAmountException($this->cents - $other->cents);
        }

        return new self($this->cents - $other->cents);
    }

    public function isGreaterThanOrEqualTo(self $other): bool
    {
        return $this->cents >= $other->cents;
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function __toString(): string
    {
        return number_format($this->cents / 100, 2);
    }
}
