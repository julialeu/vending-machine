<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Unit\Domain\Aggregate;

use PHPUnit\Framework\TestCase;
use VendingMachine\Domain\VendingMachine\Aggregate\CoinInventory;
use VendingMachine\Domain\VendingMachine\Aggregate\ProductInventory;
use VendingMachine\Domain\VendingMachine\Aggregate\VendingMachine;
use VendingMachine\Domain\VendingMachine\Entity\Product;
use VendingMachine\Domain\VendingMachine\Exception\InsufficientChangeException;
use VendingMachine\Domain\VendingMachine\Exception\InsufficientFundsException;
use VendingMachine\Domain\VendingMachine\Exception\ProductNotAvailableException;
use VendingMachine\Domain\VendingMachine\Service\ChangeCalculator;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class VendingMachineTest extends TestCase
{
    private function buildMachine(
        int $waterStock = 1,
        int $fiveCents = 10,
        int $tenCents = 10,
        int $twentyFiveCents = 10,
    ): VendingMachine {
        $products = new ProductInventory();
        $products->stock(new Product(ProductSelector::WATER, 'Water', Money::fromCents(65)), $waterStock);

        $coins = new CoinInventory();
        $coins->set(Coin::FIVE_CENTS, $fiveCents);
        $coins->set(Coin::TEN_CENTS, $tenCents);
        $coins->set(Coin::TWENTY_FIVE_CENTS, $twentyFiveCents);

        return new VendingMachine($products, $coins, new ChangeCalculator());
    }

    public function test_inserted_total_starts_at_zero(): void
    {
        $machine = $this->buildMachine();
        $this->assertTrue($machine->insertedTotal()->isZero());
    }

    public function test_insert_coin_increases_total(): void
    {
        $machine = $this->buildMachine();
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TEN_CENTS);

        $this->assertTrue($machine->insertedTotal()->equals(Money::fromCents(35)));
    }

    public function test_return_coins_empties_session_and_returns_inserted_coins(): void
    {
        $machine = $this->buildMachine();
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TEN_CENTS);

        $returned = $machine->returnCoins();

        $this->assertCount(2, $returned);
        $this->assertTrue($machine->insertedTotal()->isZero());
    }

    public function test_select_product_with_exact_change_returns_empty_change(): void
    {
        $machine = $this->buildMachine();
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TEN_CENTS);
        $machine->insertCoin(Coin::FIVE_CENTS);

        $purchase = $machine->selectProduct(ProductSelector::WATER);

        $this->assertSame('Water', $purchase->product()->name());
        $this->assertTrue($purchase->change()->isEmpty());
    }

    public function test_select_product_with_overpayment_returns_correct_change(): void
    {
        $machine = $this->buildMachine();
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);

        $purchase = $machine->selectProduct(ProductSelector::WATER);

        $this->assertTrue($purchase->change()->total()->equals(Money::fromCents(10)));
    }

    public function test_select_product_empties_inserted_coins(): void
    {
        $machine = $this->buildMachine();
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);

        $machine->selectProduct(ProductSelector::WATER);

        $this->assertTrue($machine->insertedTotal()->isZero());
    }

    public function test_select_product_throws_when_insufficient_funds(): void
    {
        $this->expectException(InsufficientFundsException::class);
        $machine = $this->buildMachine();
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->selectProduct(ProductSelector::WATER);
    }

    public function test_insufficient_funds_leaves_coins_in_session(): void
    {
        $machine = $this->buildMachine();
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);

        try {
            $machine->selectProduct(ProductSelector::WATER);
        } catch (InsufficientFundsException) {
        }

        $this->assertTrue($machine->insertedTotal()->equals(Money::fromCents(25)));
    }

    public function test_select_product_throws_when_out_of_stock(): void
    {
        $this->expectException(ProductNotAvailableException::class);
        $machine = $this->buildMachine(waterStock: 0);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->selectProduct(ProductSelector::WATER);
    }

    public function test_select_product_throws_when_cannot_make_change(): void
    {
        $this->expectException(InsufficientChangeException::class);
        $machine = $this->buildMachine(fiveCents: 0, tenCents: 0, twentyFiveCents: 0);
        $machine->insertCoin(Coin::ONE_EURO);
        $machine->selectProduct(ProductSelector::WATER);
    }

    public function test_stock_product_makes_it_selectable(): void
    {
        $products = new ProductInventory();
        $coins = new CoinInventory();
        $coins->set(Coin::FIVE_CENTS, 10);
        $coins->set(Coin::TEN_CENTS, 10);
        $coins->set(Coin::TWENTY_FIVE_CENTS, 10);
        $machine = new VendingMachine($products, $coins, new ChangeCalculator());

        $machine->stockProduct(new Product(ProductSelector::JUICE, 'Juice', Money::fromCents(100)), 1);
        $machine->insertCoin(Coin::ONE_EURO);

        $purchase = $machine->selectProduct(ProductSelector::JUICE);
        $this->assertSame('Juice', $purchase->product()->name());
    }

    public function test_stock_coin_makes_it_available_for_change(): void
    {
        $machine = $this->buildMachine(fiveCents: 0, tenCents: 0, twentyFiveCents: 0);
        $machine->stockCoin(Coin::TEN_CENTS, 1);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);
        $machine->insertCoin(Coin::TWENTY_FIVE_CENTS);

        $purchase = $machine->selectProduct(ProductSelector::WATER);

        $this->assertTrue($purchase->change()->total()->equals(Money::fromCents(10)));
    }
}
