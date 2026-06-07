<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Integration\UseCase;

use PHPUnit\Framework\TestCase;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinRequest;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinUseCase;
use VendingMachine\Application\UseCase\ReturnCoins\ReturnCoinsUseCase;
use VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;

final class ReturnCoinsUseCaseTest extends TestCase
{
    private InsertCoinUseCase $insertCoin;
    private ReturnCoinsUseCase $returnCoins;

    protected function setUp(): void
    {
        $repository = new InMemoryVendingMachineRepository();
        $this->insertCoin = new InsertCoinUseCase($repository);
        $this->returnCoins = new ReturnCoinsUseCase($repository);
    }

    public function test_returns_all_inserted_coins(): void
    {
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(10));

        $response = $this->returnCoins->execute();

        $this->assertFalse($response->isEmpty());
        $this->assertContains('0.25', $response->returnedCoins);
        $this->assertContains('0.10', $response->returnedCoins);
    }

    public function test_returns_empty_when_no_coins_inserted(): void
    {
        $response = $this->returnCoins->execute();
        $this->assertTrue($response->isEmpty());
    }

    public function test_second_return_is_empty_after_first(): void
    {
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->returnCoins->execute();

        $response = $this->returnCoins->execute();
        $this->assertTrue($response->isEmpty());
    }
}
