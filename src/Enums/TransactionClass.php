<?php

namespace Devinweb\LaravelPaytabs\Enums;

abstract class TransactionClass
{
    const ECOM = 'ecom';
    const MOTO = 'moto';
    const RECURRING = 'recurring';

    /**
     * Return the supported classes as array.
     *
     * @return array
     */
    public static function values(): array
    {
        return [self::ECOM];
    }
}
