<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Repositories;

use App\Models\User;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * UserRepository
 * php version 8.4
 *
 * @category repositories
 * @package CryptoBalanceWallet
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
final readonly class UserRepository implements UserRepositoryInterface
{
    public function findById(int $userId): ?User
    {
        return User::query()->where('id', $userId)->first();
    }

    /**
     * @return Collection<int, User>
     */
    public function getList(): Collection
    {
        return User::with(['wallets'])->get();
    }

    public function findByUuid(string $userUuid): ?User
    {
        return User::query()->where('uuid', $userUuid)->first();
    }
}
