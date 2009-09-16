<?php
/**
 * Horde_Service_Twitter_Request_Oauth class wraps sending requests to Twitter's
 * REST API using OAuth authentication.
 *
 * Copyright 2009 The Horde Project (http://www.horde.org)
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 * @category Horde
 * @package Horde_Service_Twitter
 */
class Horde_Service_Twitter_Request_Basic extends Horde_Service_Twitter_Request
{

    protected $_twitter;

    public function __construct($twitter)
    {
        $this->_twitter = $twitter;
    }

    public function get($url, $params = array())
    {
        $key = md5($url . 'get' . serialize($params) . $this->_twitter->auth->username);
        $cache = $this->_twitter->responseCache;
        if (!empty($cache) && $results = $cache->get($key, $this->_twitter->cacheLifetime)) {
            return $results;
        }
        $client = new Horde_Http_Client();
        try {
            $response = $client->get($url, array('Authorization' => $this->_twitter->auth->buildAuthorizationHeader()));
        } catch (Horde_Http_Client_Exception $e) {
            throw new Horde_Service_Twitter_Exception($e);
        }

        $body = $response->getBody();
        if ($response->code >= 400 && $response->code <= 500) {
            throw new Horde_Service_Twitter_Exception($body);
        }
        if (!empty($cache)) {
            $cache->set($key, $body);
        }

        return $body;
    }

    public function post($url, $params = array())
    {
        $client = new Horde_Http_Client();
        try {
            $response = $client->post($url, $params, array('Authorization' => $this->_twitter->auth->buildAuthorizationHeader()));
        } catch (Horde_Http_Client_Exception $e) {
            throw new Horde_Service_Twitter_Exception($e);
        }

        if ($response->code >= 400 && $response->code <= 500) {
            throw new Horde_Service_Twitter_Exception($body);
        }
        return $response->getBody();
    }

}
