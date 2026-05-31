<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Exceptions\Wallet;

use Bugfix666\CryptoBalanceWallet\Exceptions\BadHttpRequestException;

/**
 * ProcessingAmountIsInvalidException
 * php version 8.4
 *
 * @category exceptions
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
final class ProcessingAmountIsInvalidException extends BadHttpRequestException
{
    protected $message = 'Processing amount "%s" is invalid.';
    public function __construct(string $amount)
    {
        parent::__construct(sprintf($this->message, $amount), $this->code, $this->getPrevious());
    }
}
