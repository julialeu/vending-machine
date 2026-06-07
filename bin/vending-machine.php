<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VendingMachine\Application\UseCase\InsertCoin\InsertCoinUseCase;
use VendingMachine\Application\UseCase\ReturnCoins\ReturnCoinsUseCase;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductUseCase;
use VendingMachine\Application\UseCase\ServiceMachine\ServiceMachineUseCase;
use VendingMachine\Infrastructure\Cli\VendingMachineCli;
use VendingMachine\Infrastructure\Persistence\InMemoryVendingMachineRepository;

$repository = new InMemoryVendingMachineRepository();

$cli = new VendingMachineCli(
    insertCoin: new InsertCoinUseCase($repository),
    selectProduct: new SelectProductUseCase($repository),
    returnCoins: new ReturnCoinsUseCase($repository),
    serviceMachine: new ServiceMachineUseCase($repository),
);

$cli->run();
