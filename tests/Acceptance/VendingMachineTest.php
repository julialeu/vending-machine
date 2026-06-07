<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Acceptance;

use PHPUnit\Framework\TestCase;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinRequest;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinUseCase;
use VendingMachine\Application\UseCase\ReturnCoins\ReturnCoinsUseCase;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductRequest;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductUseCase;
use VendingMachine\Application\UseCase\ServiceMachine\ServiceMachineRequest;
use VendingMachine\Application\UseCase\ServiceMachine\ServiceMachineUseCase;
use VendingMachine\Domain\VendingMachine\Exception\InsufficientChangeException;
use VendingMachine\Domain\VendingMachine\Exception\InsufficientFundsException;
use VendingMachine\Domain\VendingMachine\Exception\ProductNotAvailableException;
use VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;

final class VendingMachineTest extends TestCase
{
    private InsertCoinUseCase $insertCoin;
    private SelectProductUseCase $selectProduct;
    private ReturnCoinsUseCase $returnCoins;
    private ServiceMachineUseCase $serviceMachine;

    protected function setUp(): void
    {
        $repository = new InMemoryVendingMachineRepository();
        $this->insertCoin = new InsertCoinUseCase($repository);
        $this->selectProduct = new SelectProductUseCase($repository);
        $this->returnCoins = new ReturnCoinsUseCase($repository);
        $this->serviceMachine = new ServiceMachineUseCase($repository);
    }

    /** Scenario 1: Buy water with exact change */
    public function test_buy_water_with_exact_coins(): void
    {
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(10));
        $this->insertCoin->execute(new InsertCoinRequest(5));

        $response = $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));

        $this->assertSame('Water', $response->productName);
        $this->assertEmpty($response->changeCoins);
    }

    /** Scenario 2: Buy juice and receive change */
    public function test_buy_juice_and_receive_change(): void
    {
        // Juice costs 1.00, insert 1.25
        $this->insertCoin->execute(new InsertCoinRequest(100));
        $this->insertCoin->execute(new InsertCoinRequest(25));

        $response = $this->selectProduct->execute(new SelectProductRequest('GET-JUICE'));

        $this->assertSame('Juice', $response->productName);
        $this->assertSame(['0.25'], $response->changeCoins);
    }

    /** Scenario 3: Cancel and get coins back */
    public function test_cancel_returns_all_inserted_coins(): void
    {
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(10));

        $response = $this->returnCoins->execute();

        $this->assertFalse($response->isEmpty());
        $this->assertContains('0.25', $response->returnedCoins);
        $this->assertContains('0.10', $response->returnedCoins);
    }

    /** Scenario 4: Insufficient funds */
    public function test_insufficient_funds_raises_exception(): void
    {
        $this->expectException(InsufficientFundsException::class);
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));
    }

    /** Scenario 5: Product out of stock */
    public function test_product_not_available_raises_exception(): void
    {
        // Drain all 5 water units
        for ($i = 0; $i < 5; $i++) {
            $this->insertCoin->execute(new InsertCoinRequest(25));
            $this->insertCoin->execute(new InsertCoinRequest(25));
            $this->insertCoin->execute(new InsertCoinRequest(25));
            $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));
        }

        $this->expectException(ProductNotAvailableException::class);
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));
    }

    /** Scenario 6: Machine cannot make change */
    public function test_insufficient_change_raises_exception(): void
    {
        $this->serviceMachine->execute(new ServiceMachineRequest(
            productStocks: [],
            coinStocks: [5 => 0, 10 => 0, 25 => 0],
        ));

        $this->expectException(InsufficientChangeException::class);
        // Water costs 0.65, insert 1.00 — needs 0.35 change but no coins
        $this->insertCoin->execute(new InsertCoinRequest(100));
        $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));
    }

    /** Scenario 7: Service mode restocks the machine */
    public function test_service_mode_restocks_and_machine_works_again(): void
    {
        $this->serviceMachine->execute(new ServiceMachineRequest(
            productStocks: ['GET-SODA' => 5],
            coinStocks: [25 => 20],
        ));

        // Soda costs 1.50, insert 6x 0.25
        for ($i = 0; $i < 6; $i++) {
            $this->insertCoin->execute(new InsertCoinRequest(25));
        }

        $response = $this->selectProduct->execute(new SelectProductRequest('GET-SODA'));

        $this->assertSame('Soda', $response->productName);
        $this->assertEmpty($response->changeCoins);
    }
}
