<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Repositories\Interfaces;

use Bugfix666\CryptoBalanceWallet\DTO\PrecisionDTO;
use Bugfix666\CryptoBalanceWallet\Enums\BlockchainEnum;
use Bugfix666\CryptoBalanceWallet\Enums\WalletCurrencyEnum;
use Bugfix666\CryptoBalanceWallet\Models\Wallet;

/**
 * PrecisionRepositoryInterface
 * php version 8.4
 *
 * @category interfaces
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
interface PrecisionRepositoryInterface
{
    public function getPrecisionByWallet(Wallet $wallet): ?PrecisionDTO;
    public function getPrecision(
        WalletCurrencyEnum $currency,
        BlockchainEnum $blockchain
    ): ?int;
    public function buildMinimumAmount(int $precision): string;
    public function lessThanMinimumAmount(string $amount, int $precision): bool;
}
