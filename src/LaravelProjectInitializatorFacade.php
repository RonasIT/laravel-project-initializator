<?php

namespace Ronas\LaravelProjectInitializator;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ronas\LaravelProjectInitializator\Skeleton\SkeletonClass
 */
class LaravelProjectInitializatorFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-project-initializator';
    }
}
