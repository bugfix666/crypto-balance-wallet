<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Repositories;

use Bugfix666\CryptoBalanceWallet\DTO\OperationDTO;
use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;
use Bugfix666\CryptoBalanceWallet\Enums\OpTypeEnum;
use Bugfix666\CryptoBalanceWallet\Models\Operation;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\OperationRepositoryInterface;
use Illuminate\Support\Collection;
use Symfony\Component\Uid\Uuid;

/**
 * OperationRepository
 * php version 8.4
 *
 * @category repositories
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
final readonly class OperationRepository implements OperationRepositoryInterface
{
    /**
     * @param int $walletId
     * @return Collection<int, Operation>
     */
    public function getList(int $walletId): Collection
    {
        return Operation::query()->where('id', $walletId)->get();
    }

    /**
     * @param int $walletId
     * @return Operation|null
     */
    public function findById(int $walletId): ?Operation
    {
        return Operation::query()->where('id', $walletId)->first();
    }

    public function findByUuid(string $opUuid, bool $lockForUpdate = false): ?Operation
    {
        $builder = Operation::query()->where('uuid', $opUuid);

        return $lockForUpdate ? $builder->lockForUpdate()->first() : $builder->first();
    }

    /**
     * @param array $walletIds
     * @return Collection<int, Operation>
     */
    public function findByUserWalletsIds(array $walletIds): Collection
    {
        return Operation::query()
            ->whereIn('wallet_id', $walletIds)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param OperationDTO $operationDTO
     * @return Operation
     */
    public function add(OperationDTO $operationDTO): Operation
    {
        return Operation::create($operationDTO->toArray())->refresh();
    }

    public function absAmount(string $amount): string
    {
        return str_replace('-', '', $amount);
    }

    public function prepareValue(string $amount, int $precision): string
    {
        $amount = $this->absAmount($amount);

        return $this->formatAmount(amount: $amount, precision: $precision);
    }

    public function isNotEnoughFunds(string $walletAmount, string $amount, int $precision): bool
    {
        return bccomp($walletAmount, $amount, $precision) < 0;
    }

    public function isHoldState(OpTypeEnum $opType, OpStateEnum $opState): bool
    {
        if ($opType === OpTypeEnum::OP_TYPE_CREDIT) {
            return $opState === OpStateEnum::OS_COMPLETE;
        }

        if ($opType === OpTypeEnum::OP_TYPE_DEBIT) {
            return in_array($opState, [
                OpStateEnum::OS_INPROCESS,
                OpStateEnum::OS_HOLD,
                OpStateEnum::OS_COMPLETE,
            ], true);
        }

        return false;
    }

    public function formatAmount(string $amount, int $precision): string
    {
        return bcadd($amount, '0', $precision);
    }

    public function isValidUuid(string $uuid): bool
    {
        return Uuid::isValid($uuid);
    }

    public function updateState(string $operationUuid, OpStateEnum $opState): Operation
    {
        $operation = $this->findByUuid($operationUuid, true);
        $operation->update([
            'op_state' => $opState,
        ]);

        return $operation->refresh();
    }
}
