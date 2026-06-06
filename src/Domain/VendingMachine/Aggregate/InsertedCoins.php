<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Aggregate;

use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;

final class InsertedCoins
{
    /** @var Coin[] */
    private array $coins = [];

    public function insert(Coin $coin): void
    {
        $this->coins[] = $coin;
    }

    public function total(): Money
    {
        return array_reduce(
            $this->coins,
            static fn(Money $carry, Coin $coin) => $carry->add($coin->toMoney()),
            Money::zero()
        );
    }

    /** @return Coin[] */
    public function releaseAll(): array
    {
        $coins = $this->coins;
        $this->coins = [];

        return $coins;
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
}
