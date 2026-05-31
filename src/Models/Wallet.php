<?php

namespace Bugfix666\CryptoBalanceWallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'uuid', 'currency', 'blockchain_id', 'amount'];

    protected $casts = [
        'amount' => 'string',
    ];

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }
}