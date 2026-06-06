<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Exception;

use VendingMachine\Domain\VendingMachine\Entity\Product;

final class ProductNotAvailableException extends DomainException
{
    public function __construct(Product $product)
    {
        parent::__construct(sprintf('Product "%s" is out of stock', $product->name()));
    }
}
