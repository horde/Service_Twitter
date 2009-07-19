<?php
/**
 * Horde_Service_Twitter_Request_* classes wrap sending requests to Twitter's
 * REST API using various authentication mechanisms.
 *
 * Copyright 2009 The Horde Project (http://www.horde.org)
 *
 * @author Michael J. Rubinsky <mrubinsk@horde.org>
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 * @category Horde
 * @package Horde_Service_Twitter
 */
abstract class Horde_Service_Twitter_Request
{
   protected $_twitter;

   public function __construct($twitter)
   {
       $this->_twitter = $twitter;
   }

   abstract public function get($url, $params = array());
   abstract public function post($url, $params = array());

}
