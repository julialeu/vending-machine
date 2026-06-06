<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\ReturnCoins;

use VendingMachine\Domain\VendingMachine\Repository\VendingMachineRepositoryInterface;
use VendingMachine\Domain\VendingMachine\ValueObject\Coin;

final class ReturnCoinsUseCase
{
    public function __construct(
        private readonly VendingMachineRepositoryInterface $repository,
    ) {
    }

    public function execute(): ReturnCoinsResponse
    {
        $machine = $this->repository->get();
        $coins = $machine->returnCoins();
        $this->repository->save($machine);

        return new ReturnCoinsResponse(
            returnedCoins: array_map(
                static fn(Coin $coin) => $coin->displayValue(),
                $coins,
            ),
        );
    }
}
