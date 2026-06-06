<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Exception;

use VendingMachine\Domain\VendingMachine\ValueObject\Money;

final class InsufficientChangeException extends DomainException
{
    public function __construct(Money $amount)
    {
        parent::__construct(sprintf('Cannot make exact change for amount: %s', $amount));
    }
}
