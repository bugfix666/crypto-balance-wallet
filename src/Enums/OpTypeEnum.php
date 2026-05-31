<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Enums;

/**
 * OpTypeEnum
 * php version 8.4
 *
 * @category enums
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
enum OpTypeEnum: int
{
    case OP_TYPE_CREDIT = 1;
    case OP_TYPE_DEBIT = 2;
}
