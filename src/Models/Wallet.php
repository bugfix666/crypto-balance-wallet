<?php

namespace Bugfix666\CryptoBalanceWallet\Models;

use Bugfix666\CryptoBalanceWallet\Enums\BlockchainEnum;
use Bugfix666\CryptoBalanceWallet\Enums\WalletCurrencyEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'uuid', 'currency', 'blockchain_id', 'amount'];

    protected $casts = [
        'amount' => 'string',
        'currency' => WalletCurrencyEnum::class,
        'blockchain_id' => BlockchainEnum::class
    ];

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }
}