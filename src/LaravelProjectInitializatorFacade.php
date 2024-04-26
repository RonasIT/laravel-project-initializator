<?php

namespace RonasIT\LaravelProjectInitializator;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RonasIT\LaravelProjectInitializator\Skeleton\SkeletonClass
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
