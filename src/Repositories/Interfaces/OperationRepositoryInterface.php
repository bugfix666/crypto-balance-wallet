<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Repositories\Interfaces;

use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;
use Bugfix666\CryptoBalanceWallet\Enums\OpTypeEnum;
use Bugfix666\CryptoBalanceWallet\Models\Operation;
use Illuminate\Support\Collection;

/**
 * OperationRepositoryInterface
 * php version 8.4
 *
 * @category interfaces
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
interface OperationRepositoryInterface
{
    public function getList(int $walletId): Collection;
    public function findByUserWalletsIds(array $walletIds): Collection;
    public function findById(int $walletId): ?Operation;
    public function absAmount(string $amount): string;
    public function prepareValue(string $amount, int $precision): string;
    public function isNotEnoughFunds(string $walletAmount, string $amount, int $precision): bool;
    public function isHoldState(OpTypeEnum $opType, OpStateEnum $opState): bool;
    public function formatAmount(string $amount, int $precision): string;
}
