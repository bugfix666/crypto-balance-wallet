<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Services;

use Bugfix666\CryptoBalanceWallet\Exceptions\User\UserNotFoundException;
use Bugfix666\CryptoBalanceWallet\Models\Operation;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\OperationRepositoryInterface;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * OperationService
 * php version 8.4
 *
 * @category services
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
readonly class OperationService
{
    public function __construct(
        private OperationRepositoryInterface $operationRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function findByUserUuid(string $uuid): Collection
    {
        $user = $this->userRepository->findByUuid($uuid);
        if (null === $user) {
            throw new UserNotFoundException();
        }

        return $this->operationRepository->findByUserWalletsIds($user->wallets->pluck('id')->toArray());
    }

    public function findByUuid(string $uuid): ?Operation
    {
        return Operation::query()->whereUuid($uuid)->first();
    }

    public function isValidUuid(string $uuid): bool
    {
        return $this->operationRepository->isValidUuid($uuid);
    }
}
