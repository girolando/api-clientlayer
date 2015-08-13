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
                if(!$appkey = \Config::get('app.apiClientKey')) throw new ApiConnectorException('Invalid AppKey or API_APPKEY is not defined at .env file!');
                if(!$appsecret = \Config::get('app.apiClientSecret')) throw new ApiConnectorException('Invalid AppSecret or API_APPSECRET is not defined at .env file!');
                if(!$url = \Config::get('app.apiEndpointUrl')) throw new ApiConnectorException('Invalid EndPointUrl or API_ENDPOINTURL is not defined at .env file!');
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
        $param = $this->generateToken($parameters, Auth::user());
        $param = ['__token' => $param];
        $return = null;
        $url = $this->ApiServer->getEndpointUrl().$url;
        $request = $curl->newRequest($method, $url, $param);
        $request->setHeader('X-Requested-With', 'XMLHttpRequest');
        $request->setHeader('language', \Config::get('app.locale'));

        $request->setOption(CURLOPT_FOLLOWLOCATION, 1);
        if($method == 'GET'){
            $parameters['__token'] = $param['__token'];
            $iurl = $curl->buildUrl($url, $parameters);
            $request->setUrl($iurl);
        }
        $return = $request->send();
        try {
            $ret = $this->translate($return->body);
            $ret->response = $return;
            return $ret;
        }catch(\Exception $e){
            return $return->body;
        }

    }


    protected function translate($token)
    {
        try{
            $trans = \JWT::decode($token, $this->ApiServer->getAppSecret(), ['HS256', 'HS384', 'ES256', 'RS384']);
        }catch(\Exception $e){
            throw new ApiConnectorException('Não foi possível decodificar a resposta ('.$e->getMessage().'). Resposta pura: '.$token);
        }
        return $trans;
    }

    public function generateToken($payload, int $user = null)
    {
        $header = ['AppKey' => $this->ApiServer->getAppKey()];
        if($this->usertoken) $header['UserKey'] = $this->usertoken;

        $tok = \JWT::encode($payload, $this->ApiServer->getAppSecret(), 'HS256', null, $header);
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
    public function get($service, array $parameters = [])
    {
        return $this->_request($service, 'GET');
    }

    /** Execute a POST request to the service url.
     * @param $service
     * @param array $parameters
     * @return \anlutro\cURL\Response|null
     * @throws ApiConnectorException
     */
    public function post($service, array $parameters = [])
    {
        return $this->_request($service, 'POST', $parameters);
    }

    /**Execute a PUT request to the service url.
     * @param $service
     * @param array $parameters
     * @return \anlutro\cURL\Response|null
     * @throws ApiConnectorException
     */
    public function put($service, array $parameters = [])
    {
        return $this->_request($service, 'PUT', $parameters);
    }

    /**Execute a DELETE request to the service url.
     * @param $service
     * @param array $parameters
     * @return \anlutro\cURL\Response|null
     * @throws ApiConnectorException
     */
    public function delete($service, array $parameters = [])
    {
        return $this->_request($service, 'DELETE', $parameters);
    }

}