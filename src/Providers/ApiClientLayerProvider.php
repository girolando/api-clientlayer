<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 27/07/2015
 * Time: 13:45
 */

namespace Andersonef\ApiClientLayer\Providers;

use Andersonef\ApiClientLayer\Services\ApiConnector;
use Illuminate\Support\ServiceProvider;

class ApiClientLayerProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ApiConnector', function($app){
            return ApiConnector::getInstance();
        });
    }
}
