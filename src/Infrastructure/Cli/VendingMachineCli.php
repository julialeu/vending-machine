<?php

declare(strict_types=1);

namespace VendingMachine\Infrastructure\Cli;

use VendingMachine\Application\UseCase\InsertCoin\InsertCoinRequest;
use VendingMachine\Application\UseCase\InsertCoin\InsertCoinUseCase;
use VendingMachine\Application\UseCase\ReturnCoins\ReturnCoinsUseCase;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductRequest;
use VendingMachine\Application\UseCase\SelectProduct\SelectProductUseCase;
use VendingMachine\Application\UseCase\ServiceMachine\ServiceMachineRequest;
use VendingMachine\Application\UseCase\ServiceMachine\ServiceMachineUseCase;
use VendingMachine\Domain\VendingMachine\Exception\DomainException;

final class VendingMachineCli
{
    public function __construct(
        private readonly InsertCoinUseCase $insertCoin,
        private readonly SelectProductUseCase $selectProduct,
        private readonly ReturnCoinsUseCase $returnCoins,
        private readonly ServiceMachineUseCase $serviceMachine,
    ) {
    }

    public function run(): void
    {
        $this->printWelcome();

        while (true) {
            $input = $this->readInput();

            if ($input === null || strtoupper($input) === 'EXIT') {
                echo "Goodbye!\n";
                break;
            }

            if ($input === '') {
                continue;
            }

            $this->handleInput($input);
        }
    }

    private function handleInput(string $input): void
    {
        try {
            $upper = strtoupper(trim($input));

            if ($this->looksLikeCoin($input)) {
                $this->handleInsertCoin($input);
                return;
            }

            match ($upper) {
                'GET-WATER', 'GET-JUICE', 'GET-SODA' => $this->handleSelectProduct($upper),
                'RETURN' => $this->handleReturnCoins(),
                'SERVICE' => $this->handleService(),
                default => $this->printUnknownCommand($input),
            };
        } catch (DomainException $e) {
            echo 'Error: ' . $e->getMessage() . "\n";
        }
    }

    private function handleInsertCoin(string $input): void
    {
        $cents = $this->parseCoinToCents($input);
        $response = $this->insertCoin->execute(new InsertCoinRequest($cents));
        echo "Coin inserted. Total: {$response->totalInserted} EUR\n";
    }

    private function handleSelectProduct(string $selector): void
    {
        $response = $this->selectProduct->execute(new SelectProductRequest($selector));

        echo "Dispensing: {$response->productName}\n";

        if (empty($response->changeCoins)) {
            echo "No change.\n";
        } else {
            echo 'Change: ' . implode(', ', $response->changeCoins) . " EUR\n";
        }
    }

    private function handleReturnCoins(): void
    {
        $response = $this->returnCoins->execute();

        if ($response->isEmpty()) {
            echo "No coins to return.\n";
        } else {
            echo 'Returned: ' . implode(', ', $response->returnedCoins) . " EUR\n";
        }
    }

    private function handleService(): void
    {
        $this->serviceMachine->execute(new ServiceMachineRequest(
            productStocks: [
                'GET-WATER' => 5,
                'GET-JUICE' => 5,
                'GET-SODA'  => 5,
            ],
            coinStocks: [
                5  => 10,
                10 => 10,
                25 => 10,
            ],
        ));

        echo "Machine restocked.\n";
    }

    private function looksLikeCoin(string $input): bool
    {
        return (bool) preg_match('/^\d+(\.\d+)?$/', trim($input));
    }

    private function parseCoinToCents(string $input): int
    {
        $parts = explode('.', trim($input));
        $whole = (int) $parts[0] * 100;
        $fractional = isset($parts[1])
            ? (int) str_pad(substr($parts[1], 0, 2), 2, '0', STR_PAD_RIGHT)
            : 0;

        return $whole + $fractional;
    }

    private function readInput(): ?string
    {
        echo '> ';
        $line = fgets(STDIN);

        return $line === false ? null : trim($line);
    }

    private function printWelcome(): void
    {
        echo <<<EOT
            ========================
             VENDING MACHINE
            ========================
             Coins:    0.05 | 0.10 | 0.25 | 1.00
             Products: GET-WATER (0.65) | GET-JUICE (1.00) | GET-SODA (1.50)
             Commands: RETURN | SERVICE | EXIT
            ========================

            EOT;
    }

    private function printUnknownCommand(string $input): void
    {
        echo "Unknown: '{$input}'. Try a coin (0.25), product (GET-WATER), RETURN, SERVICE, or EXIT.\n";
    }
}
