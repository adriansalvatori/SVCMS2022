<?php

/**
 * Abstract service provider class
 */

namespace PH\Support;

abstract class ServiceProvider
{
    /**
     * The application instance.
     */
    protected $app;

    /**
     * Create a new service provider instance.
     *
     * @param $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
