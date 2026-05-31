<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Enums;

use Bugfix666\CryptoBalanceWallet\Enums\Traits\TEnumToArray;

/**
 * OpStateEnum
 * php version 8.4
 *
 * @category enums
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
enum OpStateEnum: int
{
    use TEnumToArray;

    case OS_INPROCESS = 1;
    case OS_COMPLETE = 2;
    case OS_FAIL = 3;
    case OS_CANCELED = 4;
    case OS_HOLD = 5;
}
