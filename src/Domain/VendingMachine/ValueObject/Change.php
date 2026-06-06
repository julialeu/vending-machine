<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\ValueObject;

final class Change
{
    /** @var Coin[] */
    private readonly array $coins;

    public function __construct(Coin ...$coins)
    {
        $this->coins = $coins;
    }

    public static function none(): self
    {
        return new self();
    }

    /** @return Coin[] */
    public function coins(): array
    {
        return $this->coins;
    }

    public function isEmpty(): bool
    {
        return empty($this->coins);
    }

    public function total(): Money
    {
        return array_reduce(
            $this->coins,
            static fn(Money $carry, Coin $coin) => $carry->add($coin->toMoney()),
            Money::zero()
        );
    }
}
