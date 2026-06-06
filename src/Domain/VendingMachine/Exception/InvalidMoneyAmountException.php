<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Exception;

final class InvalidMoneyAmountException extends DomainException
{
    public function __construct(int $cents)
    {
        parent::__construct(sprintf('Money amount cannot be negative: %d cents', $cents));
    }
}
