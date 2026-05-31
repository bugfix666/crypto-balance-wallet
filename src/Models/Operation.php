<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Models;

use Bugfix666\CryptoBalanceWallet\Enums\BlockchainEnum;
use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;
use Bugfix666\CryptoBalanceWallet\Enums\OpTypeEnum;
use Bugfix666\CryptoBalanceWallet\Enums\WalletCurrencyEnum;
use App\Models\User;
use Bugfix666\CryptoBalanceWallet\Models\Wallet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Bugfix666\CryptoBalanceWallet\Models\Operation
 * php version 8.4
 *
 * @category models
 * @package CryptoBalance
 * @author bugfix666 <appscenter@proton.me>
 * @license GPLv3 License
 * @link https://github.com/bugfix666/crypto-balance-wallet
 * @property int $id
 * @property string $uuid
 * @property string $amount
 * @property string $currency
 * @property BlockchainEnum|null $blockchain_id
 * @property int $wallet_id
 * @property int $op_type
 * @property int $op_state
 * @property array<array-key, mixed>|null $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $user
 * @property-read Wallet $wallet
 * @method static Builder<static>|Operation newModelQuery()
 * @method static Builder<static>|Operation newQuery()
 * @method static Builder<static>|Operation onlyTrashed()
 * @method static Builder<static>|Operation query()
 * @method static Builder<static>|Operation whereAmount($value)
 * @method static Builder<static>|Operation whereBlockchainId($value)
 * @method static Builder<static>|Operation whereCreatedAt($value)
 * @method static Builder<static>|Operation whereCurrency($value)
 * @method static Builder<static>|Operation whereData($value)
 * @method static Builder<static>|Operation whereDeletedAt($value)
 * @method static Builder<static>|Operation whereId($value)
 * @method static Builder<static>|Operation whereOpState($value)
 * @method static Builder<static>|Operation whereOpType($value)
 * @method static Builder<static>|Operation whereUpdatedAt($value)
 * @method static Builder<static>|Operation whereUuid($value)
 * @method static Builder<static>|Operation whereWalletId($value)
 * @method static Builder<static>|Operation withTrashed()
 * @method static Builder<static>|Operation withoutTrashed()
 */
class Operation extends Model
{
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'wallet_id',
        'currency',
        'blockchain_id',
        'uuid',
        'op_type',
        'op_state',
        'amount',
        'data',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'wallet_id' => 'int',
            'data' => 'json',
            'currency' => WalletCurrencyEnum::class,
            'blockchain_id' => BlockchainEnum::class,
            'op_state' => OpStateEnum::class,
            'op_type' => OpTypeEnum::class,
        ];
    }

    /**
     * @return BelongsTo<Wallet, self>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
