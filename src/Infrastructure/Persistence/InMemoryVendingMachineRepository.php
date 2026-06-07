<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Persistence;

use VendingMachine\Domain\VendingMachine\Aggregate\CoinInventory;
use VendingMachine\Domain\VendingMachine\Aggregate\ProductInventory;
use VendingMachine\Domain\VendingMachine\Aggregate\VendingMachine;
use VendingMachine\Domain\VendingMachine\Entity\Product;
use VendingMachine\Domain\VendingMachine\Repository\VendingMachineRepositoryInterface;
use VendingMachine\Domain\VendingMachine\Service\ChangeCalculator;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class InMemoryVendingMachineRepository implements VendingMachineRepositoryInterface
{
    private ?VendingMachine $machine = null;

    public function get(): VendingMachine
    {
        if ($this->machine === null) {
            $this->machine = $this->initialize();
        }

        return $this->machine;
    }

    public function save(VendingMachine $machine): void
    {
        $this->machine = $machine;
    }

    private function initialize(): VendingMachine
    {
        $productInventory = new ProductInventory();
        $productInventory->stock(new Product(ProductSelector::WATER, 'Water', Money::fromCents(65)), 5);
        $productInventory->stock(new Product(ProductSelector::JUICE, 'Juice', Money::fromCents(100)), 5);
        $productInventory->stock(new Product(ProductSelector::SODA, 'Soda', Money::fromCents(150)), 5);

        $coinInventory = new CoinInventory();
        $coinInventory->set(Coin::FIVE_CENTS, 10);
        $coinInventory->set(Coin::TEN_CENTS, 10);
        $coinInventory->set(Coin::TWENTY_FIVE_CENTS, 10);
        $coinInventory->set(Coin::ONE_EURO, 0);

        return new VendingMachine($productInventory, $coinInventory, new ChangeCalculator());
    }
}
