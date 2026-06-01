<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\DTO;

use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;

/**
 * BalanceProcessCallbackDTO
 * php version 8.4
 *
 * @category DTO
 * @package CryptoBalanceWallet
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
readonly final class BalanceProcessCallbackDTO
{
    public function __construct(
        private OpStateEnum $opState,
        private string $operationUuid,
    ) {
    }

    public function getOpState(): OpStateEnum
    {
        return $this->opState;
    }

    public function getOperationUuid(): string
    {
        return $this->operationUuid;
    }
}
