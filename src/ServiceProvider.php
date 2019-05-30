<?php

namespace Vestervang\AgileResource;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Add the Cors middleware to the router.
     *
     */
    public function boot()
    {
        require_once __DIR__ . '/Macros/Order.php';
        require_once __DIR__ . '/Macros/Paginator.php';

    }
}