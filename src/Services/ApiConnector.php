<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 13/07/2015
 * Time: 08:53
 */

namespace Andersonef\ApiClientLayer\Services;


use Andersonef\ApiClientLayer\Entities\Remote\ApiServer;
use Andersonef\ApiClientLayer\Exceptions\ApiConnectorException;
use Illuminate\Auth\Guard;
use anlutro\cURL\cURL;
use Illuminate\Support\Facades\Auth;

class ApiConnector {

    protected $ApiServer;
    protected $usertoken;
    private static $instance;


    private function __construct(ApiServer $apiServer)
    {
        $this->ApiServer = $apiServer;
    }

    public static function getInstance($apiServer = null)
    {
        if(!self::$instance)
        {
            if(!$apiServer)
            {
                if(!$appkey = env('API_APPKEY')) throw new ApiConnectorException('Invalid AppKey or API_APPKEY is not defined at .env file!');
                if(!$appsecret = env('API_APPSECRET')) throw new ApiConnectorException('Invalid AppSecret or API_APPSECRET is not defined at .env file!');
                if(!$url = env('API_ENDPOINTURL')) throw new ApiConnectorException('Invalid EndPointUrl or API_ENDPOINTURL is not defined at .env file!');
                if($url{strlen($url)-1} == '/') throw new ApiConnectorException('Your EndPoint Url should not end with slash.');
                $apiServer = new ApiServer($appkey, $appsecret, $url);
            }
            self::$instance = new ApiConnector($apiServer);
        }
        return self::$instance;
    }


    private function _request($url, $method = 'POST', $parameters = [])
    {
        if($url{0} != '/') throw new ApiConnectorException('Your service url must begin with slash.');
        $response = null;
        $curl = new cURL();
        $iurl = $curl->buildUrl($this->ApiServer->getEndpointUrl().$url);
        $param = $this->generateToken($parameters, Auth::user());
        $param = ['__token' => $param];
        $return = null;
        switch($method){
            case 'GET':
                $iurl = $curl->buildUrl($this->ApiServer->getEndpointUrl().$url, $param);
                $return = $curl->get($iurl);
                break;
            case 'POST':
                $return = $curl->post($url, $param);
                break;
            case 'PUT':
                $return = $curl->put($url, $param);
                break;
            case 'DELETE':
                $return = $curl->delete($url, $param);
                break;
            default:
                throw new ApiConnectorException('Method '.$method.' not allowed');
                break;
        }
        return $return;
    }


    public function generateToken($payload, int $user = null)
    {
        $tok = \JWT::encode($payload, $this->ApiServer->getAppSecret(), 'HS256', null, ['AppKey' => $this->ApiServer->getAppSecret()]);
        return $tok;
    }

    public function withUser($usertoken)
    {
        $this->usertoken = $usertoken;
        return $this;
    }

    /** Execute a POST request to the service url.
     * @param $service
     * @return \anlutro\cURL\Response|null
     * @throws ApiConnectorException
     */
    public function get($service)
    {
        return $this->_request($service, 'GET');
    }

    /** Execute a POST request to the service url.
     * @param $service
     * @param array $parameters
     * @return \anlutro\cURL\Response|null
     * @throws ApiConnectorException
     */
    public function post($service, $parameters = [])
    {
        return $this->_request($service, 'POST', $parameters);
    }

    /**Execute a PUT request to the service url.
     * @param $service
     * @param array $parameters
     * @return \anlutro\cURL\Response|null
     * @throws ApiConnectorException
     */
    public function put($service, $parameters = [])
    {
        return $this->_request($service, 'PUT', $parameters);
    }

    /**Execute a DELETE request to the service url.
     * @param $service
     * @param array $parameters
     * @return \anlutro\cURL\Response|null
     * @throws ApiConnectorException
     */
    public function delete($service, $parameters = [])
    {
        return $this->_request($service, 'DELETE', $parameters);
    }

}