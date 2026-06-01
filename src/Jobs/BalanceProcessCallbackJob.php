<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Jobs;

use Bugfix666\CryptoBalanceWallet\DTO\BalanceProcessCallbackDTO;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\InvalidUuidStringException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\OperationNotFoundException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\ProcessingAmountIsInvalidException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\UnsupportedBlockchainOrCurrencyException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\WalletCurrencyPrecisionNotSetException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\WalletNotFoundException;
use Bugfix666\CryptoBalanceWallet\Services\OperationService;
use Bugfix666\CryptoBalanceWallet\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * BalanceProcessCallbackJob
 * php version 8.4
 *
 * @category jobs
 * @package CryptoBalanceWallet
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
class BalanceProcessCallbackJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param BalanceProcessCallbackDTO $balanceProcessCallbackDTO
     */
    public function __construct(
        private readonly BalanceProcessCallbackDTO $balanceProcessCallbackDTO,
    ) {
    }

    /**
     * Execute the job.
     *
     * @param WalletService $walletService
     * @param OperationService $operationService
     *
     * @return void
     * @throws InvalidUuidStringException
     * @throws ProcessingAmountIsInvalidException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws WalletNotFoundException
     * @throws OperationNotFoundException
     */
    public function handle(
        WalletService $walletService,
        OperationService $operationService,
    ): void {
        $operation = $operationService->findByUuid(
            $this->balanceProcessCallbackDTO->getOperationUuid()
        );
        if (null === $operation) {
            throw new OperationNotFoundException();
        }

        $walletService->addBalance(
            amount: $operation->amount,
            walletUuid: $operation->wallet->uuid,
            opState: $this->balanceProcessCallbackDTO->getOpState(),
            operation: $operation
        );
    }
}
