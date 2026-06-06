<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Exception;

final class InvalidCoinException extends DomainException
{
    public function __construct(int $cents)
    {
        parent::__construct(sprintf('Coin with value %d cents is not accepted by this machine', $cents));
    }
}
