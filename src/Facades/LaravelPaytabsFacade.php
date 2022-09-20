<?php

namespace Devinweb\LaravelPaytabs\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Devinweb\LaravelPaytabs\Skeleton\SkeletonClass
 */
class LaravelPaytabsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-paytabs';
    }
}
