<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Exception;

final class InvalidProductSelectorException extends DomainException
{
    public function __construct(string $selector)
    {
        parent::__construct(sprintf('Unknown product selector: "%s"', $selector));
    }
}
