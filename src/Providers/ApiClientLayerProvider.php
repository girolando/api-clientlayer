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
        $this->app->singleton('Andersonef\ApiClientLayer\Services\ApiConnector', function($app){
            if(!$appkey = \Config::get('app.apiClientKey')) throw new ApiConnectorException('Invalid AppKey or API_APPKEY is not defined at .env file!');
            if(!$appsecret = \Config::get('app.apiClientSecret')) throw new ApiConnectorException('Invalid AppSecret or API_APPSECRET is not defined at .env file!');
            if(!$url = \Config::get('app.apiEndpointUrl')) throw new ApiConnectorException('Invalid EndPointUrl or API_ENDPOINTURL is not defined at .env file!');
            if($url{strlen($url)-1} == '/') throw new ApiConnectorException('Your EndPoint Url should not end with slash.');
            $apiServer = new ApiServer($appkey, $appsecret, $url);
            return ApiConnector::getInstance($apiServer);
        });


        //cachorro
    }
}
