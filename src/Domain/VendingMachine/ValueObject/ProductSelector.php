<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\ValueObject;

use VendingMachine\Domain\VendingMachine\Exception\InvalidProductSelectorException;

enum ProductSelector: string
{
    case WATER = 'GET-WATER';
    case JUICE = 'GET-JUICE';
    case SODA  = 'GET-SODA';

    public static function fromString(string $selector): self
    {
        return self::tryFrom($selector) ?? throw new InvalidProductSelectorException($selector);
    }
}
