<?php

namespace Bugfix666\CryptoBalanceWallet\Providers;

use Bugfix666\CryptoBalanceWallet\Services\WalletService;
use Illuminate\Support\ServiceProvider;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\OperationRepositoryInterface;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\PrecisionRepositoryInterface;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\UserRepositoryInterface;
use Bugfix666\CryptoBalanceWallet\Repositories\Interfaces\WalletRepositoryInterface;
use Bugfix666\CryptoBalanceWallet\Repositories\OperationRepository;
use Bugfix666\CryptoBalanceWallet\Repositories\PrecisionRepository;
use Bugfix666\CryptoBalanceWallet\Repositories\UserRepository;
use Bugfix666\CryptoBalanceWallet\Repositories\WalletRepository;

class WalletServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OperationRepositoryInterface::class, OperationRepository::class);
        $this->app->bind(PrecisionRepositoryInterface::class, PrecisionRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);

        $this->app->singleton(WalletService::class, function ($app) {
            return new WalletService(
                $app->make(WalletRepositoryInterface::class),
                $app->make(PrecisionRepositoryInterface::class),
                $app->make(OperationRepositoryInterface::class)
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/wallet.php' => config_path('wallet.php'),
        ], 'wallet-config');

        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'wallet-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}