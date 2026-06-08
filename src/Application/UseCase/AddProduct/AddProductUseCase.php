<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\AddProduct;

use VendingMachine\Domain\VendingMachine\Entity\Product;
use VendingMachine\Domain\VendingMachine\Repository\VendingMachineRepositoryInterface;
use VendingMachine\Domain\VendingMachine\ValueObject\Money;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class AddProductUseCase
{
    public function __construct(
        private readonly VendingMachineRepositoryInterface $repository,
    ) {
    }

    public function execute(AddProductRequest $request): void
    {
        $machine = $this->repository->get();

        $machine->stockProduct(
            new Product(
                ProductSelector::fromString($request->selectorValue),
                $request->name,
                Money::fromCents($request->priceCents),
            ),
            $request->quantity,
        );

        $this->repository->save($machine);
    }
}
