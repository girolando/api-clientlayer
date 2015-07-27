<?php
/**
 * Created by PhpStorm.
 * User: ansilva
 * Date: 27/07/2015
 * Time: 09:58
 */

namespace Andersonef\ApiClientLayer\Entities\Remote;


class ApiServer
{
    protected $appKey;
    protected $appSecret;
    protected $endpointUrl;

    public function __construct($appKey, $appSecret, $endpointUrl)
    {
        $this->appKey =$appKey;
        $this->appSecret = $appSecret;
        $this->endpointUrl = $endpointUrl;
    }

    /**
     * @return mixed
     */
    public function getAppKey()
    {
        return $this->appKey;
    }

    /**
     * @return mixed
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * @return mixed
     */
    public function getEndpointUrl()
    {
        return $this->endpointUrl;
    }

}