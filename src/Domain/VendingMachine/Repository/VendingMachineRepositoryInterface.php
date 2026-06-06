<?php

declare(strict_types=1);

namespace VendingMachine\Domain\VendingMachine\Repository;

use VendingMachine\Domain\VendingMachine\Aggregate\VendingMachine;

interface VendingMachineRepositoryInterface
{
    public function get(): VendingMachine;

    public function save(VendingMachine $machine): void;
}
