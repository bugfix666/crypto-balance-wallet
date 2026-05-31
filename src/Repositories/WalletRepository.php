<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Repositories;

use Bugfix666\CryptoBalanceWallet\DTO\OperationDTO;
use Bugfix666\CryptoBalanceWallet\DTO\PrecisionDTO;
use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;
use Bugfix666\CryptoBalanceWallet\Enums\OpTypeEnum;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\InvalidOperationStateException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\InvalidWalletIdException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\NotEnoughFundsException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\WalletCurrencyPrecisionNotSetException;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\WalletRollbackException;
use Bugfix666\CryptoBalanceWallet\Models\Operation;
use Bugfix666\CryptoBalanceWallet\Models\Wallet;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\OperationRepositoryInterface;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\PrecisionRepositoryInterface;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final readonly class WalletRepository implements WalletRepositoryInterface
{
    public function __construct(
        private OperationRepositoryInterface $operationRepository,
        private PrecisionRepositoryInterface $precisionRepository,
    ) {
    }

    public function findById(int $walletId, bool $lockForUpdate = false): ?Wallet
    {
        $builder = Wallet::query()->where('id', $walletId);
        return $lockForUpdate ? $builder->lockForUpdate()->first() : $builder->first();
    }

    public function findByUuid(string $walletUuid, bool $lockForUpdate = false): ?Wallet
    {
        $builder = Wallet::query()->where('uuid', $walletUuid);
        return $lockForUpdate ? $builder->lockForUpdate()->first() : $builder->first();
    }

    /**
     * @return array{Wallet, PrecisionDTO}
     * @throws InvalidWalletIdException
     * @throws WalletCurrencyPrecisionNotSetException
     */
    private function getWalletAndPrecision(string $walletUuid, bool $lockForUpdate = true): array
    {
        $wallet = $this->findByUuid($walletUuid, $lockForUpdate);
        if (!$wallet) {
            throw new InvalidWalletIdException();
        }

        $precisionDTO = $this->precisionRepository->getPrecisionByWallet($wallet);
        if (!$precisionDTO) {
            throw new WalletCurrencyPrecisionNotSetException();
        }

        return [$wallet, $precisionDTO];
    }

    /**
     * @throws InvalidOperationStateException
     * @throws WalletRollbackException
     * @throws Throwable
     */
    private function changeOperationState(
        Operation $operation,
        OpStateEnum $newState,
        Wallet $wallet,
        PrecisionDTO $precision,
    ): Operation {
        // Блокируем строку операции, чтобы избежать конкурентных изменений
        $lockedOperation = Operation::query()
            ->whereKey($operation->id)
            ->lockForUpdate()
            ->firstOrFail();

        // Разрешён только переход из HOLD в другое состояние
        if ($lockedOperation->op_state !== OpStateEnum::OS_HOLD) {
            throw new InvalidOperationStateException('Operation already processed');
        }

        return match ($newState) {
            OpStateEnum::OS_COMPLETE => $this->completeOperation($lockedOperation),
            OpStateEnum::OS_CANCELED => $this->cancelOperation($lockedOperation, $wallet, $precision),
            default => throw new InvalidOperationStateException('Unsupported state transition'),
        };
    }

    /**
     * @throws InvalidOperationStateException
     */
    private function completeOperation(Operation $lockedOperation): Operation
    {
        $updated = Operation::query()
            ->whereKey($lockedOperation->id)
            ->where('op_state', OpStateEnum::OS_HOLD)
            ->update(['op_state' => OpStateEnum::OS_COMPLETE]);

        if (!$updated) {
            throw new InvalidOperationStateException('Failed to complete operation');
        }

        return $lockedOperation->fresh();
    }

    /**
     * @throws WalletRollbackException
     * @throws InvalidOperationStateException
     */
    private function cancelOperation(Operation $lockedOperation, Wallet $wallet, PrecisionDTO $precision): Operation
    {
        // Возвращаем зарезервированные средства в зависимости от типа операции
        $rollbackAmount = match ($lockedOperation->op_type) {
            OpTypeEnum::OP_TYPE_CREDIT => bcmul($lockedOperation->amount, '-1', $precision->getPrecision()),
            OpTypeEnum::OP_TYPE_DEBIT => $this->operationRepository->absAmount($lockedOperation->amount),
            default => throw new InvalidOperationStateException()
        };

        $updated = Wallet::query()
            ->whereKey($wallet->id)
            ->increment('amount', $rollbackAmount);

        if (!$updated) {
            throw new WalletRollbackException();
        }

        $updatedOp = Operation::query()
            ->whereKey($lockedOperation->id)
            ->where('op_state', OpStateEnum::OS_HOLD)
            ->update(['op_state' => OpStateEnum::OS_CANCELED]);

        if (!$updatedOp) {
            throw new InvalidOperationStateException('Failed to cancel operation');
        }

        return $lockedOperation->fresh();
    }

    /**
     * @throws Throwable
     */
    public function credit(
        string $amount,
        string $walletUuid,
        OpStateEnum $opState,
        ?Operation $operation = null,
    ): ?Operation {
        return DB::transaction(function () use ($amount, $walletUuid, $opState, $operation) {
            [$wallet, $precisionDTO] = $this->getWalletAndPrecision(
                walletUuid: $walletUuid,
                lockForUpdate: true
            );

            // Создание новой операции кредита
            if ($operation === null) {
                // Для кредита баланс всегда увеличивается, дополнительная проверка не требуется
                Wallet::query()->whereKey($wallet->id)->increment('amount', $amount);

                return $this->operationRepository->add(
                    new OperationDTO(
                        uuid: Str::uuid()->toString(),
                        amount: $amount,
                        precision: $precisionDTO,
                        walletId: $wallet->id,
                        opType: OpTypeEnum::OP_TYPE_CREDIT,
                        opState: $opState,
                        createdAt: now(),
                    )
                );
            }

            // Обработка уже существующей операции (смена состояния)
            return $this->changeOperationState($operation, $opState, $wallet, $precisionDTO);
        }, 3);
    }

    /**
     * @throws Throwable
     */
    public function debit(
        string $amount,
        string $walletUuid,
        OpStateEnum $opState,
        ?Operation $operation = null,
    ): ?Operation {
        return DB::transaction(function () use ($amount, $walletUuid, $opState, $operation) {
            [$wallet, $precisionDTO] = $this->getWalletAndPrecision($walletUuid, lockForUpdate: true);

            // Создание новой операции дебета
            if ($operation === null) {
                // Резервируем средства только если операция создаётся в статусе HOLD
                if ($this->operationRepository->isHoldState(OpTypeEnum::OP_TYPE_DEBIT, $opState)) {
                    $updated = Wallet::query()
                        ->whereKey($wallet->id)
                        ->whereRaw('amount - ? >= 0', [$amount])
                        ->decrement('amount', $amount);

                    if (!$updated) {
                        throw new NotEnoughFundsException();
                    }
                }

                // Сумма в DTO сохраняется отрицательной для дебета
                $negativeAmount = bcmul($amount, '-1', $precisionDTO->getPrecision());

                return $this->operationRepository->add(
                    new OperationDTO(
                        uuid: Str::uuid()->toString(),
                        amount: $negativeAmount,
                        precision: $precisionDTO,
                        walletId: $wallet->id,
                        opType: OpTypeEnum::OP_TYPE_DEBIT,
                        opState: $opState,
                        createdAt: now(),
                    )
                );
            }

            // Обработка уже существующей операции
            return $this->changeOperationState($operation, $opState, $wallet, $precisionDTO);
        }, 3);
    }
}
