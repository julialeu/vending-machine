<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Exception\InvalidProductSelectorException;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class ProductSelectorTest extends TestCase
{
    public function test_fromString_returns_correct_case(): void
    {
        $this->assertSame(ProductSelector::WATER, ProductSelector::fromString('GET-WATER'));
        $this->assertSame(ProductSelector::JUICE, ProductSelector::fromString('GET-JUICE'));
        $this->assertSame(ProductSelector::SODA, ProductSelector::fromString('GET-SODA'));
    }

    public function test_fromString_throws_for_invalid_value(): void
    {
        $this->expectException(InvalidProductSelectorException::class);
        ProductSelector::fromString('GET-INVALID');
    }

    public function test_value_matches_string_selector(): void
    {
        $this->assertSame('GET-WATER', ProductSelector::WATER->value);
        $this->assertSame('GET-JUICE', ProductSelector::JUICE->value);
        $this->assertSame('GET-SODA', ProductSelector::SODA->value);
    }
}
