<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\DTO;

use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;
use Bugfix666\CryptoBalanceWallet\Enums\OpTypeEnum;
use Illuminate\Support\Carbon;

/**
 * OperationDTO
 * php version 8.4
 *
 * @category DTO
 * @package CryptoBalanceWallet
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
readonly final class OperationDTO
{
    public function __construct(
        private string $uuid,
        private string $amount,
        private PrecisionDTO $precision,
        private int $walletId,
        private OpTypeEnum $opType,
        private OpStateEnum $opState,
        private Carbon $createdAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'amount' => $this->amount,
            'currency' => $this->precision->getCurrency(),
            'blockchain_id' => $this->precision->getBlockchain(),
            'wallet_id' => $this->walletId,
            'op_type' => $this->opType,
            'op_state' => $this->opState,
            'created_at' => $this->createdAt
        ];
    }
}
