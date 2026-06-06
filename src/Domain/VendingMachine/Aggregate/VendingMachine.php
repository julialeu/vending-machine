<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Aggregate;

use VendingMachine\Domain\VendingMachine\Entity\Product;
use VendingMachine\Domain\VendingMachine\Exception\InsufficientFundsException;
use VendingMachine\Domain\VendingMachine\Exception\ProductNotAvailableException;
use VendingMachine\Domain\VendingMachine\Service\ChangeCalculator;
use VendingMachine\Domain\VendingMachine\ValueObject\Change;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;
use VendingMachine\Domain\VendingMachine\ValueObject\Purchase;

final class VendingMachine
{
    private InsertedCoins $insertedCoins;

    public function __construct(
        private readonly ProductInventory $productInventory,
        private readonly CoinInventory $coinInventory,
        private readonly ChangeCalculator $changeCalculator,
    ) {
        $this->insertedCoins = new InsertedCoins();
    }

    public function insertCoin(Coin $coin): void
    {
        $this->insertedCoins->insert($coin);
    }

    /** @return Coin[] */
    public function returnCoins(): array
    {
        return $this->insertedCoins->releaseAll();
    }

    public function selectProduct(ProductSelector $selector): Purchase
    {
        $product = $this->productInventory->find($selector);

        if (!$this->productInventory->hasAvailable($selector)) {
            throw new ProductNotAvailableException($product);
        }

        $inserted = $this->insertedCoins->total();

        if (!$inserted->isGreaterThanOrEqualTo($product->price())) {
            throw new InsufficientFundsException($product->price(), $inserted);
        }

        $changeAmount = $inserted->subtract($product->price());
        $change = $changeAmount->isZero()
            ? Change::none()
            : $this->changeCalculator->calculate($changeAmount, $this->coinInventory);

        $this->productInventory->dispense($selector);

        foreach ($this->insertedCoins->releaseAll() as $insertedCoin) {
            $this->coinInventory->add($insertedCoin);
        }

        foreach ($change->coins() as $changeCoin) {
            $this->coinInventory->remove($changeCoin);
        }

        return new Purchase($product, $change);
    }

    public function insertedTotal(): Money
    {
        return $this->insertedCoins->total();
    }

    public function stockProduct(Product $product, int $quantity): void
    {
        $this->productInventory->stock($product, $quantity);
    }

    public function updateProductStock(ProductSelector $selector, int $quantity): void
    {
        $this->productInventory->updateQuantity($selector, $quantity);
    }

    public function stockCoin(Coin $coin, int $quantity): void
    {
        $this->coinInventory->set($coin, $quantity);
    }
}
