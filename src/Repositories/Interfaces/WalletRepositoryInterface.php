<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Repositories\Interfaces;

use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;
use Bugfix666\CryptoBalanceWallet\Models\Operation;
use Bugfix666\CryptoBalanceWallet\Models\Wallet;

/**
 * WalletRepositoryInterface
 * php version 8.4
 *
 * @category interfaces
 * @package CryptoBalanceWallet
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
interface WalletRepositoryInterface
{
    public function findById(int $walletId, bool $lockForUpdate = false): ?Wallet;
    public function findByUuid(string $walletUuid, bool $lockForUpdate = false): ?Wallet;
    public function debit(
        string $amount,
        string $walletUuid,
        OpStateEnum $opState,
        ?Operation $operation = null
    ): ?Operation;

    public function credit(
        string $amount,
        string $walletUuid,
        OpStateEnum $opState,
        ?Operation $operation = null
    ): ?Operation;
}
