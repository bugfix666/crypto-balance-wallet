<?php

namespace Bugfix666\CryptoBalanceWallet\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Bugfix666\CryptoBalanceWallet\Providers\WalletServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            WalletServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}