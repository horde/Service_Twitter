<?php
/**
 * Horde_Service_Twitter class abstracts communication with Twitter's
 * rest interface.
 *
 * Copyright 2009 The Horde Project (http://www.horde.org)
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 * @category Horde
 * @package Horde_Service_Twitter
 */
class Horde_Service_Twitter
{

    /**
     * Cache for the various objects we lazy load in __get()
     *
     * @var hash of Horde_Service_Twitter_* objects
     */
    protected $_objCache = array();

    /**
     * Configuration values
     *
     * @var array
     */
    protected $_config;

    /**
     * Type of authentication (Oauth, Basic)
     *
     * @var string
     */
    protected $_authType;

    /**
     * Can't lazy load the auth or request class since we need to know early if
     *  we are OAuth or Basic
     *
     * @var Horde_Service_Twitter_Auth
     */
    protected $_auth;

    /**
     *
     * @var Horde_Service_Twitter_Request
     */
    protected $_request;

    /**
     * Const'r
     *
     * @param array $config  Configuration parameters:
     *   <pre>
     *     'oauth'    - Horde_Oauth object if using Oauth
     *     'username' - if using Basic auth
     *     'password' - if using Basic auth
     *   </pre>
     */
    public function __construct($config)
    {
        // TODO: Check for req'd config
        $this->_config = $config;

        // Need to determine the type of authentication we will be using early..
        if (!empty($config['oauth'])) {
            // OAuth
            $this->_authType = 'Oauth';
            $params = array('oauth' => $config['oauth']);
        } elseif (!empty($config['username']) && !empty($config['password'])) {
            // Http_Basic
            $this->_authType = 'Basic';
            $params = array('username' => $config['username'],
                            'password' => $config['password']);
        }

        $aclass = 'Horde_Service_Twitter_Auth_' . $this->_authType;
        $rclass = 'Horde_Service_Twitter_Request_' . $this->_authType;

        $this->_auth = new $aclass($this, $params);
        $this->_request = new $rclass($this);
    }

    /**
     * Lazy load the twitter classes.
     *
     * @param string $value  The lowercase representation of the subclass.
     *
     * @throws Horde_Service_Twitter_Exception
     * @return Horde_Service_Twitter_* object.
     */
    public function __get($value)
    {
        // First, see if it's an allowed protected value.
        switch ($value) {
        case 'auth':
            return $this->_auth;
        case 'request':
            return $this->_request;
        }

        // If not, assume it's a method/action class...
        $class = 'Horde_Service_Twitter_' . ucfirst($value);
        if (!empty($this->_objCache[$class])) {
            return $this->_objCache[$class];
        }

        if (!class_exists($class)) {
            throw new Horde_Service_Twitter_Exception(sprintf("%s class not found", $class));
        }


        $this->_objCache[$class] = new $class($this);
        return $this->_objCache[$class];
    }

}
