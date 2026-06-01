<?php

declare(strict_types=1);

namespace Bugfix666\CryptoBalanceWallet\Models;

use Bugfix666\CryptoBalanceWallet\Enums\BlockchainEnum;
use Bugfix666\CryptoBalanceWallet\Enums\WalletCurrencyEnum;
use App\Models\User;
use Bugfix666\CryptoBalanceWallet\Repositories\PrecisionRepository;
use Database\Factories\Bugfix666\CryptoBalanceWallet\Models\WalletFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bugfix666\CryptoBalanceWallet\Models\Wallet
 * php version 8.4
 *
 * @category models
 * @package CryptoBalance
 * @author bugfix666 <appscenter@proton.me>
 * @license GPLv3 License
 * @link https://github.com/bugfix/crypto-balance-wallet
 * @property int $id
 * @property string $uuid
 * @property string $amount
 * @property WalletCurrencyEnum|null $currency
 * @property BlockchainEnum|null $blockchain_id
 * @property int $user_id
 * @property-read User $user
 * @method static WalletFactory factory($count = null, $state = [])
 * @method static Builder<static>|Wallet newModelQuery()
 * @method static Builder<static>|Wallet newQuery()
 * @method static Builder<static>|Wallet query()
 * @method static Builder<static>|Wallet whereAmount($value)
 * @method static Builder<static>|Wallet whereBlockchainId($value)
 * @method static Builder<static>|Wallet whereCurrency($value)
 * @method static Builder<static>|Wallet whereId($value)
 * @method static Builder<static>|Wallet whereUserId($value)
 * @method static Builder<static>|Wallet whereUuid($value)
 * @mixin Eloquent
 */
class Wallet extends Eloquent
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'amount',
        'currency',
        'blockchain_id',
        'user_id',
        'uuid',
    ];
    protected $casts = [
        'amount' => 'string',
        'currency' => WalletCurrencyEnum::class,
        'blockchain_id' => BlockchainEnum::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPrecision(): ?int
    {
        return app(PrecisionRepository::class)->getPrecision($this->currency, $this->blockchain_id);
    }
}
