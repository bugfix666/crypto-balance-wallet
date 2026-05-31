<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Repositories;

use Bugfix666\CryptoBalanceWallet\DTO\PrecisionDTO;
use Bugfix666\CryptoBalanceWallet\Enums\BlockchainEnum;
use Bugfix666\CryptoBalanceWallet\Enums\WalletCurrencyEnum;
use Bugfix666\CryptoBalanceWallet\Exceptions\Wallet\UnsupportedBlockchainOrCurrencyException;
use Bugfix666\CryptoBalanceWallet\Models\Wallet;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\PrecisionRepositoryInterface;
use ValueError;

/**
 * PrecisionRepository
 * php version 8.4
 *
 * @category repositories
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
final readonly class PrecisionRepository implements PrecisionRepositoryInterface
{
    private const array PRECISION = [
        BlockchainEnum::FIAT->value => [
            WalletCurrencyEnum::USD->value => 2,
        ],
        BlockchainEnum::BTC->value => [
            WalletCurrencyEnum::BTC->value => 8,
        ],
        BlockchainEnum::TRC20->value => [
            WalletCurrencyEnum::USDT->value => 6,
        ],
    ];

    /**
     * @param Wallet $wallet
     *
     * @return ?PrecisionDTO
     * @throws UnsupportedBlockchainOrCurrencyException
     */
    public function getPrecisionByWallet(Wallet $wallet): ?PrecisionDTO
    {
        try {
            $currency = WalletCurrencyEnum::from($wallet->currency->value);
            $blockchain = BlockchainEnum::from($wallet->blockchain_id->value);
        } catch (ValueError) {
            throw new UnsupportedBlockchainOrCurrencyException();
        }
        $precision = $this->getPrecision(
            currency: $currency,
            blockchain: $blockchain
        );

        return null !== $precision ? new PrecisionDTO(
            currency: $currency,
            blockchain: $blockchain,
            precision: $precision
        ) : null;
    }

    public function getPrecision(
        WalletCurrencyEnum $currency,
        BlockchainEnum $blockchain
    ): ?int {
        return self::PRECISION[$blockchain->value][$currency->value] ?? null;
    }

    public function buildMinimumAmount(int $precision): string
    {
        return '0.' . str_pad('', $precision - 1, '0') . '1';
    }

    public function lessThanMinimumAmount(string $amount, int $precision): bool
    {
        return bccomp(
            $amount,
            $this->buildMinimumAmount($precision),
            $precision
        ) < 0;
    }
}
