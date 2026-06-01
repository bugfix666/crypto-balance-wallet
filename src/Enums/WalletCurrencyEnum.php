<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Enums;

use Bugfix666\CryptoBalanceWallet\Enums\Traits\TEnumToArray;

/**
 * WalletCurrencyEnum
 * php version 8.4
 *
 * @category enums
 * @package CryptoBalanceWallet
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
enum WalletCurrencyEnum: string
{
    use TEnumToArray;

    case BTC = 'BTC';
    case USDT = 'USDT';
    case USD = 'USD';
}
