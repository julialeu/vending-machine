<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\ServiceMachine;

use VendingMachine\Domain\VendingMachine\Repository\VendingMachineRepositoryInterface;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;
use VendingMachine\Domain\VendingMachine\ValueObject\ProductSelector;

final class ServiceMachineUseCase
{
    public function __construct(
        private readonly VendingMachineRepositoryInterface $repository,
    ) {
    }

    public function execute(ServiceMachineRequest $request): void
    {
        $machine = $this->repository->get();

        foreach ($request->productStocks as $selectorValue => $quantity) {
            $machine->updateProductStock(
                ProductSelector::fromString($selectorValue),
                $quantity,
            );
        }

        foreach ($request->coinStocks as $cents => $quantity) {
            $machine->stockCoin(Coin::fromCents($cents), $quantity);
        }

        $this->repository->save($machine);
    }
}
