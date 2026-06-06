<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Aggregate;

use VendingMachine\Domain\VendingMachine\Exception\InvalidStockQuantityException;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;

final class CoinInventory
{
    /** @var array<int, int> keyed by coin value in cents */
    private array $counts = [];

    public function add(Coin $coin): void
    {
        $this->counts[$coin->value] = ($this->counts[$coin->value] ?? 0) + 1;
    }

    public function remove(Coin $coin): void
    {
        $this->counts[$coin->value] = max(0, ($this->counts[$coin->value] ?? 0) - 1);
    }

    public function set(Coin $coin, int $quantity): void
    {
        if ($quantity < 0) {
            throw new InvalidStockQuantityException($quantity);
        }

        $this->counts[$coin->value] = $quantity;
    }

    public function countOf(Coin $coin): int
    {
        return $this->counts[$coin->value] ?? 0;
    }
}
