<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * UserRepositoryInterface
 * php version 8.4
 *
 * @category interfaces
 * @package  CryptoBalance
 * @author   bugfix666 <appscenter@proton.me>
 * @license  GPLv3 License
 * @link     https://github.com/bugfix666/crypto-balance-wallet
 */
interface UserRepositoryInterface
{
    public function findById(int $userId): ?User;
    public function findByUuid(string $userUuid): ?User;
    public function getList(): Collection;
}
