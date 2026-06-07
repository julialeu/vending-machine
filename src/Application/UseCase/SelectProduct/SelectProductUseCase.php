<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\SelectProduct;

use VendingMachine\Domain\VendingMachine\Repository\VendingMachineRepositoryInterface;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class SelectProductUseCase
{
    public function __construct(
        private readonly VendingMachineRepositoryInterface $repository,
    ) {
    }

    public function execute(SelectProductRequest $request): SelectProductResponse
    {
        $selector = ProductSelector::fromString($request->productSelector);

        $machine = $this->repository->get();
        $purchase = $machine->selectProduct($selector);
        $this->repository->save($machine);

        return new SelectProductResponse(
            productName: $purchase->product()->name(),
            changeCoins: array_map(
                static fn (Coin $coin) => $coin->displayValue(),
                $purchase->change()->coins(),
            ),
        );
    }
}
