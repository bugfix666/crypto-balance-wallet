<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Services;

use App\Models\User;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * UserService
 * php version 8.4
 *
 * @category services
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
readonly class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function getList(): Collection
    {
        return $this->userRepository->getList();
    }

    public function findByUuid(string $uuid): ?User
    {
        return $this->userRepository->findByUuid($uuid);
    }
}
