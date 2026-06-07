<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Integration\UseCase;

use PHPUnit\Framework\TestCase;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinRequest;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinUseCase;
use VendingMachine\Domain\VendingMachine\Exception\InvalidCoinException;
use VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;

final class InsertCoinUseCaseTest extends TestCase
{
    private InsertCoinUseCase $useCase;

    protected function setUp(): void
    {
        $this->useCase = new InsertCoinUseCase(new InMemoryVendingMachineRepository());
    }

    public function test_inserts_valid_coin_and_returns_total(): void
    {
        $response = $this->useCase->execute(new InsertCoinRequest(25));
        $this->assertSame('0.25', $response->totalInserted);
    }

    public function test_accumulates_multiple_coins(): void
    {
        $this->useCase->execute(new InsertCoinRequest(25));
        $response = $this->useCase->execute(new InsertCoinRequest(25));
        $this->assertSame('0.50', $response->totalInserted);
    }

    public function test_throws_for_invalid_denomination(): void
    {
        $this->expectException(InvalidCoinException::class);
        $this->useCase->execute(new InsertCoinRequest(15));
    }
}
