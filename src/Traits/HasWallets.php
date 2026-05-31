<?php

namespace Bugfix666\CryptoBalanceWallet\Traits;

use Bugfix666\CryptoBalanceWallet\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasWallets
{

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'user_id', 'id');
    }
}
