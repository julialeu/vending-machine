<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Integration\UseCase;

use PHPUnit\Framework\TestCase;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinRequest;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinUseCase;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductRequest;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductUseCase;
use VendingMachine\Domain\VendingMachine\Exception\InsufficientFundsException;
use VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;

final class SelectProductUseCaseTest extends TestCase
{
    private InsertCoinUseCase $insertCoin;
    private SelectProductUseCase $selectProduct;

    protected function setUp(): void
    {
        $repository = new InMemoryVendingMachineRepository();
        $this->insertCoin = new InsertCoinUseCase($repository);
        $this->selectProduct = new SelectProductUseCase($repository);
    }

    public function test_dispenses_product_with_exact_coins(): void
    {
        // Water costs 65 cents: 25 + 25 + 10 + 5
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(10));
        $this->insertCoin->execute(new InsertCoinRequest(5));

        $response = $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));

        $this->assertSame('Water', $response->productName);
        $this->assertEmpty($response->changeCoins);
    }

    public function test_dispenses_product_and_returns_change(): void
    {
        // Water costs 65 cents, insert 75
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(25));

        $response = $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));

        $this->assertSame('Water', $response->productName);
        $this->assertSame(['0.10'], $response->changeCoins);
    }

    public function test_throws_when_insufficient_funds(): void
    {
        $this->expectException(InsufficientFundsException::class);
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));
    }
}
