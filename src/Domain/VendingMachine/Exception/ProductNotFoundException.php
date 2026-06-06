<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Exception;

use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class ProductNotFoundException extends DomainException
{
    public function __construct(ProductSelector $selector)
    {
        parent::__construct(sprintf('No product configured for selector: "%s"', $selector->value));
    }
}
