<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

/**
 * BadHttpRequestException
 * php version 8.4
 *
 * @category exceptions
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
class BadHttpRequestException extends Exception
{
    protected $code = HttpFoundationResponse::HTTP_BAD_REQUEST;
}
