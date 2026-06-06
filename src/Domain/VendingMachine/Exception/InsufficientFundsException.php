<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Exception;

use VendingMachine\Domain\VendingMachine\ValueObject\Money;

final class InsufficientFundsException extends DomainException
{
    public function __construct(Money $required, Money $inserted)
    {
        parent::__construct(sprintf(
            'Insufficient funds: required %s, inserted %s',
            $required,
            $inserted
        ));
    }
}
