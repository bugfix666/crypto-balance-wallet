<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Services;

use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\InvalidUuidStringException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\ProcessingAmountIsInvalidException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\UnsupportedBlockchainOrCurrencyException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\WalletCurrencyPrecisionNotSetException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\WalletNotFoundException;
use Bugfix666\CryptoBalanceWallet\Models\Operation;
use Bugfix666\CryptoBalanceWallet\Models\Wallet;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\OperationRepositoryInterface;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\PrecisionRepositoryInterface;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\WalletRepositoryInterface;

/**
 * WalletService
 * php version 8.4
 *
 * @category services
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
readonly class WalletService
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
        private PrecisionRepositoryInterface $precisionRepository,
        private OperationRepositoryInterface $operationRepository,
    ) {
    }

    /**
     * @param string $uuid
     * @return Wallet|null
     */
    public function findByUuid(string $uuid): ?Wallet
    {
        return $this->walletRepository->findByUuid($uuid);
    }

    /**
     * @param string $amount
     * @param string $walletUuid
     * @param OpStateEnum $opState
     * @param Operation|null $operation
     *
     * @return Operation|null
     * @throws InvalidUuidStringException
     * @throws ProcessingAmountIsInvalidException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws WalletNotFoundException
     */
    public function addBalance(
        string $amount,
        string $walletUuid,
        OpStateEnum $opState = OpStateEnum::OS_COMPLETE,
        ?Operation $operation = null
    ): ?Operation {
        $amount = $this->validate(
            amount: $amount,
            walletUuid: $walletUuid,
        );

        return $this->walletRepository->credit(
            amount: $amount,
            walletUuid: $walletUuid,
            opState: $opState,
            operation : $operation
        );
    }

    /**
     * @param string $amount
     * @param string $walletUuid
     * @param OpStateEnum $opState
     * @param Operation|null $operation
     *
     * @return Operation|null
     * @throws InvalidUuidStringException
     * @throws ProcessingAmountIsInvalidException
     * @throws UnsupportedBlockchainOrCurrencyException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws WalletNotFoundException
     */
    public function subBalance(
        string $amount,
        string $walletUuid,
        OpStateEnum $opState = OpStateEnum::OS_COMPLETE,
        ?Operation $operation = null
    ): ?Operation {
        $amount = $this->validate(
            amount: $amount,
            walletUuid: $walletUuid
        );

        return $this->walletRepository->debit(
            amount: $amount,
            walletUuid: $walletUuid,
            opState: $opState,
            operation: $operation
        );
    }

    /**
     * @throws InvalidUuidStringException
     * @throws ProcessingAmountIsInvalidException
     * @throws WalletCurrencyPrecisionNotSetException
     * @throws WalletNotFoundException
     * @throws UnsupportedBlockchainOrCurrencyException
     */
    private function validate(string $amount, string $walletUuid): string
    {
        // 1. Проверка UUID
        if (false === $this->operationRepository->isValidUuid($walletUuid)) {
            throw new InvalidUuidStringException();
        }

        // 2. Проверка существования кошелька
        $wallet = $this->walletRepository->findByUuid($walletUuid);
        if (null === $wallet) {
            throw new WalletNotFoundException();
        }

        // 3. Получение точности
        $precisionDTO = $this->precisionRepository->getPrecisionByWallet($wallet);
        if (null === $precisionDTO) {
            throw new WalletCurrencyPrecisionNotSetException();
        }

        $precision = $precisionDTO->getPrecision();

        // 4. Проверка, что сумма положительна (больше нуля)
        if (bccomp($amount, '0', $precision) <= 0) {
            throw new ProcessingAmountIsInvalidException($amount);
        }

        // 5. Подготовка значения и проверка минимальной суммы
        $value = $this->operationRepository->prepareValue($amount, $precision);
        if ($this->precisionRepository->lessThanMinimumAmount($value, $precision)) {
            throw new ProcessingAmountIsInvalidException($value);
        }

        return $amount;
    }
}
