<?php

declare(strict_types=1);

namespace VendingMachine\Application\UseCase\ServiceMachine;

final class ServiceMachineRequest
{
    /**
     * @param array<string, int> $productStocks selector value => quantity (e.g. ['GET-WATER' => 10])
     * @param array<int, int>    $coinStocks    coin cents => quantity (e.g. [5 => 20, 25 => 10])
     */
    public function __construct(
        public readonly array $productStocks,
        public readonly array $coinStocks,
    ) {
    }
}
