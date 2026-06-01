# Crypto Balance Wallet

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bugfix666/crypto-balance-wallet.svg?style=flat-square)](https://packagist.org/packages/bugfix666/crypto-balance-wallet)
[![Total Downloads](https://img.shields.io/packagist/dt/bugfix666/crypto-balance-wallet.svg?style=flat-square)](https://packagist.org/packages/bugfix666/crypto-balance-wallet)
[![License](https://img.shields.io/packagist/l/bugfix666/crypto-balance-wallet.svg?style=flat-square)](https://packagist.org/packages/bugfix666/crypto-balance-wallet)
[![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?logo=php&logoColor=white)]()
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)]()
[![Redis](https://img.shields.io/badge/Redis-Queue-red?logo=redis&logoColor=white)]()

Manage cryptocurrency wallets and balances with **hold mechanism** (two-phase transactions) – ideal for crypto exchanges, payment systems, and financial applications.

---

## 🚀 Features

- ✅ **Credit/Debit operations** with precise decimal math (BCMath)
- ✅ **Hold / Commit / Cancel** pattern (two‑phase transactions)
- ✅ **Currency precision** per wallet (BTC 18 decimals, USDT 2, etc.)
- ✅ **Atomic balance updates** using database locks
- ✅ **Eloquent models** with proper relationships
- ✅ **Repository pattern** for easy mocking and extension
- ✅ **Artisan commands** for CLI deposits/withdrawals
- ✅ **Ready-to-use HTTP API** (optional)
- ✅ **Laravel 12+** support

---

## 📦 Installation

```bash
composer require bugfix666/crypto-balance-wallet
```

### 1. Publish migrations (recommended)

```bash
php artisan vendor:publish --tag=wallet-migrations
php artisan migrate
```

### 2. Publish configuration

```bash
php artisan vendor:publish --tag=wallet-config
```

This creates `config/wallet.php` where you can define precision per currency, blockchain mappings, etc.


---

## ⚙️ Configuration

Edit `config/wallet.php`:

```php
return [
    'precision' => [
        'BTC' => 18,
        'ETH' => 18,
        'USDT' => 2,
        'TRX'  => 6,
    ],
    'blockchain_map' => [
        'BTC'  => 'bitcoin',
        'ETH'  => 'ethereum',
        'TRX'  => 'tron',
    ],
];
```

> **Note:** The package uses `WalletCurrencyEnum` and `BlockchainEnum` from its own namespace. You can override them via config if needed.

---

## 🧠 Core Concepts

### Two‑Phase Transaction (Hold Pattern)

| State      | Description                                 |
|------------|---------------------------------------------|
| `HOLD`     | Funds are reserved (debit) or added (credit) but not finalised |
| `COMPLETE` | Confirms the operation – no further balance change |
| `CANCELED` | Reverts a HOLD operation, returning funds   |

**Credit (deposit)** example:
1. Create HOLD → balance increases immediately
2. Later `COMPLETE` or `CANCELED` (only changes operation state)

**Debit (withdrawal)** example:
1. Create HOLD → balance decreases immediately (reserved)
2. Later `COMPLETE` (no change) or `CANCELED` (returns funds)

> ⚠️ Always perform the second call (COMPLETE/CANCELED) using the **same Operation object** returned by the first call.

---

## 🔧 Usage

### Using the `WalletService` (recommended)

```php
use Bugfix666\CryptoBalanceWallet\Services\WalletService;
use Bugfix666\CryptoBalanceWallet\Enums\OpStateEnum;

$walletService = app(WalletService::class);
$walletUuid = '550e8400-e29b-41d4-a716-446655440000';

// Deposit $100.00 immediately (COMPLETE)
$operation = $walletService->addBalance('100.00', $walletUuid, OpStateEnum::OS_COMPLETE);

// Deposit with hold (to be confirmed later)
$holdOperation = $walletService->addBalance('50.00', $walletUuid, OpStateEnum::OS_HOLD);

// Later: confirm the hold
$completedOp = $walletService->addBalance('50.00', $walletUuid, OpStateEnum::OS_COMPLETE, $holdOperation);

// Or cancel the hold (refund)
$canceledOp = $walletService->addBalance('50.00', $walletUuid, OpStateEnum::OS_CANCELED, $holdOperation);
```

### Withdrawals (debit)

```php
// Reserve $30.00 (HOLD)
$holdOp = $walletService->subBalance('30.00', $walletUuid, OpStateEnum::OS_HOLD);

// Confirm the withdrawal
$completedOp = $walletService->subBalance('30.00', $walletUuid, OpStateEnum::OS_COMPLETE, $holdOp);

// Or cancel (return funds to wallet)
$canceledOp = $walletService->subBalance('30.00', $walletUuid, OpStateEnum::OS_CANCELED, $holdOp);
```


---

## 🧪 Testing

```bash
composer test
```

The package uses **Orchestra Testbench** for isolated testing.  
You can also run the original feature tests (after publishing) inside your Laravel project:

```bash
php artisan test --filter=WalletTest
```

---

## 📂 Package Structure

```
src/
├── Contracts/            # Repository interfaces
├── Enums/                # OpStateEnum, OpTypeEnum, CurrencyEnum, BlockchainEnum
├── Exceptions/           # Wallet & User exceptions
├── Models/               # Wallet, Operation
├── Repositories/         # WalletRepository, OperationRepository, PrecisionRepository
├── Services/             # WalletService, OperationService, UserService
├── DTO/                  # OperationDTO, PrecisionDTO
├── Console/Commands/     # DepositCommand, WithdrawCommand, ListWalletsCommand
├── Jobs/                 # BalanceProcessCallbackJob (async)
└── Providers/            # WalletServiceProvider
```

---

## 🔐 Error Handling

All exceptions extend Laravel’s base exceptions where possible. Typical exceptions:

| Exception                                | When thrown                                      |
|------------------------------------------|--------------------------------------------------|
| `WalletNotFoundException`                | Wallet UUID does not exist                       |
| `NotEnoughFundsException`                | Insufficient balance for debit                   |
| `InvalidOperationStateException`         | Trying to complete/cancel non‑HOLD operation     |
| `ProcessingAmountIsInvalidException`     | Amount is zero, negative, or below minimum       |
| `WalletCurrencyPrecisionNotSetException` | Missing precision configuration for currency     |
| `WalletRollbackException`                | Database rollback failed during cancel           |

---

## 🧩 Requirements

- PHP 8.4+
- Laravel 12+
- BCMath PHP extension
- Database with row locking support (MySQL 8+, PostgreSQL)

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing`)
3. Run `composer install` and `composer test` to ensure tests pass
4. Commit your changes
5. Open a Pull Request

Please follow [PSR-12](https://www.php-fig.org/psr/psr-12/) and use `composer pint` for code style.

---

## 📄 License

**GPL-3.0-only** – see [LICENSE](LICENSE) file for details.

---

## ❓ FAQ

**Q: How do I create a wallet for a user?**  
A: Use the `Wallet::create()` method after ensuring your User model exists. The package does **not** enforce a specific `User` model – you can attach wallets to any model via `user_id`.

**Q: Can I use different precision per wallet of the same currency?**  
A: Yes – implement your own `PrecisionRepositoryInterface` and bind it in your service provider. The default implementation reads from `config/wallet.php`.

**Q: Are operations automatically cleaned up?**  
A: No – HOLD operations stay forever. You should implement a scheduled job to cancel stale holds if needed.

**Q: Is this safe for high‑concurrency?**  
A: Yes – all critical sections use `SELECT ... FOR UPDATE` row‑locks inside database transactions.

---

## 📫 Support

Open an issue on [GitHub Issues](https://github.com/bugfix666/crypto-balance-wallet/issues) or contact `appscenter@proton.me`.

---

**Built with ❤️ by bugfix666**  
*Stable, auditable, and production‑ready.*
