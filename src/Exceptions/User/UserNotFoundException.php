<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Exceptions\User;

use Bugfix666\CryptoBalanceWallet\Exceptions\BadHttpRequestException;

/**
 * UserNotFoundException
 * php version 8.4
 *
 * @category exceptions
 * @package CryptoBalanceWallet
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
final class UserNotFoundException extends BadHttpRequestException
{
    protected $message = 'User not found';
}
