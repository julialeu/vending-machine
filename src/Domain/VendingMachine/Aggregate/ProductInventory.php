<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Aggregate;

use VendingMachine\Domain\VendingMachine\Entity\Product;
use VendingMachine\Domain\VendingMachine\Exception\InvalidStockQuantityException;
use VendingMachine\Domain\VendingMachine\Exception\ProductNotAvailableException;
use VendingMachine\Domain\VendingMachine\Exception\ProductNotFoundException;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class ProductInventory
{
    /** @var array<string, array{product: Product, quantity: int}> */
    private array $stock = [];

    public function stock(Product $product, int $quantity): void
    {
        if ($quantity < 0) {
            throw new InvalidStockQuantityException($quantity);
        }

        $this->stock[$product->selector()->value] = [
            'product'  => $product,
            'quantity' => $quantity,
        ];
    }

    public function find(ProductSelector $selector): Product
    {
        if (!isset($this->stock[$selector->value])) {
            throw new ProductNotFoundException($selector);
        }

        return $this->stock[$selector->value]['product'];
    }

    public function dispense(ProductSelector $selector): Product
    {
        if (!isset($this->stock[$selector->value])) {
            throw new ProductNotFoundException($selector);
        }

        if ($this->stock[$selector->value]['quantity'] <= 0) {
            throw new ProductNotAvailableException($this->stock[$selector->value]['product']);
        }

        $this->stock[$selector->value]['quantity']--;

        return $this->stock[$selector->value]['product'];
    }

    public function hasAvailable(ProductSelector $selector): bool
    {
        return isset($this->stock[$selector->value])
            && $this->stock[$selector->value]['quantity'] > 0;
    }
}
