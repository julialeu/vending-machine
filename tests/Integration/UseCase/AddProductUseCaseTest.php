<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Integration\UseCase;

use PHPUnit\Framework\TestCase;
use VendingMachine\Application\UseCase\AddProduct\AddProductRequest;
use VendingMachine\Application\UseCase\AddProduct\AddProductUseCase;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinRequest;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinUseCase;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductRequest;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductUseCase;
use VendingMachine\Domain\VendingMachine\Exception\InvalidProductSelectorException;
use VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;

final class AddProductUseCaseTest extends TestCase
{
    private InMemoryVendingMachineRepository $repository;
    private AddProductUseCase $addProduct;
    private InsertCoinUseCase $insertCoin;
    private SelectProductUseCase $selectProduct;

    protected function setUp(): void
    {
        $this->repository = new InMemoryVendingMachineRepository();
        $this->addProduct = new AddProductUseCase($this->repository);
        $this->insertCoin = new InsertCoinUseCase($this->repository);
        $this->selectProduct = new SelectProductUseCase($this->repository);
    }

    public function test_added_product_can_be_selected(): void
    {
        $this->addProduct->execute(new AddProductRequest(
            selectorValue: 'GET-SODA',
            name: 'Soda',
            priceCents: 150,
            quantity: 3,
        ));

        $this->insertCoin->execute(new InsertCoinRequest(100));
        $this->insertCoin->execute(new InsertCoinRequest(25));
        $this->insertCoin->execute(new InsertCoinRequest(25));

        $response = $this->selectProduct->execute(new SelectProductRequest('GET-SODA'));

        $this->assertSame('Soda', $response->productName);
        $this->assertEmpty($response->changeCoins);
    }

    public function test_replaces_existing_product_stock(): void
    {
        $this->addProduct->execute(new AddProductRequest(
            selectorValue: 'GET-WATER',
            name: 'Water',
            priceCents: 65,
            quantity: 10,
        ));

        // Buy 6 waters — only possible if stock was replaced to 10 (default was 5)
        for ($i = 0; $i < 6; $i++) {
            $this->insertCoin->execute(new InsertCoinRequest(25));
            $this->insertCoin->execute(new InsertCoinRequest(25));
            $this->insertCoin->execute(new InsertCoinRequest(25));
            $this->selectProduct->execute(new SelectProductRequest('GET-WATER'));
        }

        $this->assertTrue(true);
    }

    public function test_throws_for_invalid_selector(): void
    {
        $this->expectException(InvalidProductSelectorException::class);
        $this->addProduct->execute(new AddProductRequest(
            selectorValue: 'GET-INVALID',
            name: 'Invalid',
            priceCents: 100,
            quantity: 5,
        ));
    }
}
