<?php

namespace Bugfix666\CryptoBalanceWallet\Database\Factories;

use Bugfix666\CryptoBalanceWallet\Enums\WalletCurrencyEnum;
use Bugfix666\CryptoBalanceWallet\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->unique()->uuid(),
            'currency' => WalletCurrencyEnum::BTC,
            'amount' => 0,
        ];
    }
}
