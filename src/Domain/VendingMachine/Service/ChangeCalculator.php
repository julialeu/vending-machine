<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Service;

use VendingMachine\Domain\VendingMachine\Aggregate\CoinInventory;
use VendingMachine\Domain\VendingMachine\Exception\InsufficientChangeException;
use VendingMachine\Domain\VendingMachine\ValueObject\Change;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;

final class ChangeCalculator
{
    public function calculate(Money $amount, CoinInventory $inventory): Change
    {
        $remaining = $amount->cents();
        $coins = [];

        $denominations = array_filter(
            Coin::cases(),
            static fn (Coin $coin) => $coin->isReturnableAsChange()
        );

        usort($denominations, static fn (Coin $a, Coin $b) => $b->value <=> $a->value);

        foreach ($denominations as $coin) {
            $available = $inventory->countOf($coin);
            $needed = min(intdiv($remaining, $coin->value), $available);

            for ($i = 0; $i < $needed; $i++) {
                $coins[] = $coin;
                $remaining -= $coin->value;
            }

            if ($remaining === 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new InsufficientChangeException($amount);
        }

        return new Change(...$coins);
    }
}
