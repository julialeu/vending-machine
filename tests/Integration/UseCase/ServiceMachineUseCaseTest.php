<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Integration\UseCase;

use PHPUnit\Framework\TestCase;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinRequest;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinUseCase;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductRequest;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductUseCase;
use VendingMachine\Application\UseCase\ServiceMachine\ServiceMachineRequest;
use VendingMachine\Application\UseCase\ServiceMachine\ServiceMachineUseCase;
use VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;

final class ServiceMachineUseCaseTest extends TestCase
{
    private InMemoryVendingMachineRepository $repository;
    private InsertCoinUseCase $insertCoin;
    private SelectProductUseCase $selectProduct;
    private ServiceMachineUseCase $serviceMachine;

    protected function setUp(): void
    {
        $this->repository = new InMemoryVendingMachineRepository();
        $this->insertCoin = new InsertCoinUseCase($this->repository);
        $this->selectProduct = new SelectProductUseCase($this->repository);
        $this->serviceMachine = new ServiceMachineUseCase($this->repository);
    }

    public function test_restocks_products_after_drain(): void
    {
        // Drain all 5 water units
        for ($i = 0; $i < 5; $i++) {
            $this->insertCoin->execute(new InsertCoinRequest(25));
            $this->insertCoin->execute(new InsertCoinRequest(25));
            $this->insertCoin->execute(new InsertCoinRequest(25));
            $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));
        }

        $this->serviceMachine->execute(new ServiceMachineRequest(
            productStocks: ['GET-WATER' => 5],
            coinStocks: [10 => 20],
        ));

        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $response = $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));

        $this->assertSame('Water', $response->productName);
    }

    public function test_restocks_coins(): void
    {
        $this->serviceMachine->execute(new ServiceMachineRequest(
            productStocks: [],
            coinStocks: [5 => 99],
        ));

        // Water costs 65 cents, insert 1 EUR — needs 35 cents change (7x5)
        $this->insertCoin->execute(new InsertCoinRequest(100));
        $response = $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));

        $this->assertSame(
            35,
            $response->changeCoins === [] ? 0 :
            array_sum(array_map(
                static fn (string $c) => (int) round((float) $c * 100),
                $response->changeCoins
            ))
        );
    }
}
