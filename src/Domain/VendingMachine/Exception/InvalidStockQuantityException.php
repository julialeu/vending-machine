<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Exception;

final class InvalidStockQuantityException extends DomainException
{
    public function __construct(int $quantity)
    {
        parent::__construct(sprintf('Stock quantity cannot be negative: %d', $quantity));
    }
}
