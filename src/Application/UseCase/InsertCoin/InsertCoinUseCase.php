<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\InsertCoin;

use VendingMachine\Domain\VendingMachine\Repository\VendingMachineRepositoryInterface;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;

final class InsertCoinUseCase
{
    public function __construct(
        private readonly VendingMachineRepositoryInterface $repository,
    ) {
    }

    public function execute(InsertCoinRequest $request): InsertCoinResponse
    {
        $coin = Coin::fromCents($request->coinCents);

        $machine = $this->repository->get();
        $machine->insertCoin($coin);
        $this->repository->save($machine);

        return new InsertCoinResponse(
            totalInserted: (string) $machine->insertedTotal(),
        );
    }
}
