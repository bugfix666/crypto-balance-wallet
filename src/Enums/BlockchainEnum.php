<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Enums;

/**
 * BlockchainEnum
 * php version 8.4
 *
 * @category enums
 * @package CryptoBalanceWallet
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
enum BlockchainEnum: int
{
    case FIAT = 0;
    case BTC = 1;
    case TRC20 = 2;
}
