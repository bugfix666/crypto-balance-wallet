<?php

namespace Bugfix666\CryptoBalanceWallet\Enums\Traits;

trait TEnumToArray
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
