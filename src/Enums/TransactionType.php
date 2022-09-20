<?php
namespace Devinweb\LaravelPaytabs\Enums;

abstract class TransactionType
{
    const SALE = 'sale';
    const AUTH = 'auth';
    const REFUND = 'refund';
    const VOID = 'void';
    const CAPTURE = 'capture';

    /**
     * Check if the given type is a follow up transaction type
     *
     * @param  string $transactionType
     * @return bool
     */
    public static function isFollowUpType($transactionType): bool
    {
        return in_array($transactionType, [self::REFUND, self::VOID, self::CAPTURE]);
    }

    /**
     * Check if the given type is a initaite transaction type
     *
     * @param  string $transactionType
     * @return bool
     */
    public static function isInitiateType($transactionType): bool
    {
        return in_array($transactionType, [self::SALE, self::AUTH]);
    }

}
