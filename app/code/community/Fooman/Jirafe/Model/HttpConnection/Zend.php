<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Fooman_Jirafe
 * @copyright   Copyright (c) 2010 Jirafe Inc (http://www.jirafe.com)
 * @copyright   Copyright (c) 2010 Fooman Limited (http://www.fooman.co.nz)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fooman_Jirafe_Model_HttpConnection_Zend extends Zend_Http_Client implements Jirafe_HttpConnection_Interface
{

    private $_base;

    /**
     * Initializes Zend_Http_Client connection.
     *
     * @param   string  $base       api url
     * @param   integer $timeout    connection timeout
     * @param   string  $useragent  client user-agent
     */
    public function __construct($base, $timeout = 10, $useragent = 'jirafe-php-client')
    {
        $this->_base = rtrim($base, '/') . '/';
        $config = array('useragent' => $useragent, 'timeout' => $timeout);
        parent::__construct($this->_base, $config);
    }

    /**
     * Makes get request to the service.
     *
     * @param   string  $path   relative resource path
     * @param   array   $query  resource query string
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function get($path, array $query = array()) 
    {
        $this->setUri($path);
        foreach($query as $parameter=>$value) {
            $this->setParameterGet($parameter,$value);
        }
        return $this->initializeResponse($this->request(self::GET));
    }

    /**
     * Makes head request to the service.
     *
     * @param   string  $path   relative resource path
     * @param   array   $query  resource query string
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function head($path, array $query = array())
    {
        $this->setUri($path);
        foreach($query as $parameter=>$value) {
            $this->setParameterGet($parameter,$value);
        }
        return $this->initializeResponse($this->request(self::HEAD));    
    }

    /**
     * Makes post request to the service.
     *
     * @param   string  $path       relative resource path
     * @param   array   $query      resource query string
     * @param   array   $parameters resource post parameters
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function post($path, array $query = array(), array $parameters = array())
    {
        $this->setUri($path);
        foreach($query as $parameter=>$value) {
            $this->setParameterGet($parameter,$value);
        }
        foreach($parameters as $parameter=>$value) {
            $this->setParameterPost($parameter,$value);
        }        
        return $this->initializeResponse($this->request(self::POST));    
    }

    /**
     * Makes put request to the service.
     *
     * @param   string  $path       relative resource path
     * @param   array   $query      resource query string
     * @param   array   $parameters resource post parameters
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function put($path, array $query = array(), array $parameters = array())
    {
        $this->setUri($path);
        foreach($query as $parameter=>$value) {
            $this->setParameterGet($parameter,$value);
        }
        foreach($parameters as $parameter=>$value) {
            $this->setParameterPost($parameter,$value);
        }        
        return $this->initializeResponse($this->request(self::PUT));     
    }

    /**
     * Makes delete request to the service.
     *
     * @param   string  $path       relative resource path
     * @param   array   $query      resource query string
     * @param   array   $parameters resource post parameters
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function delete($path, array $query = array(), array $parameters = array())
    {
        $this->setUri($path);
        foreach($query as $parameter=>$value) {
            $this->setParameterGet($parameter,$value);
        }
        foreach($parameters as $parameter=>$value) {
            $this->setParameterPost($parameter,$value);
        }        
        return $this->initializeResponse($this->request(self::DELETE));     
    }
     
    
    /**
     * Initializes response object.
     *
     * @param   Zend_Http_Response  $response
     *
     * @return  Jirafe_HttpConnection_Response
     */
    protected function initializeResponse(Zend_Http_Response $response)
    {
        return new Jirafe_HttpConnection_Response($response->getBody(), $response->getHeaders(), $response->isError()?$response->getStatus():0, $response->getMessage());
    }    

    /**
     * Set the URI for the next request, combine relative path with base url
     *
     * @see Zend_Http_Client::setUri()
     */
    public function setUri($path)
    {
        parent::setUri($this->_base . ltrim($path, '/'));
    }
    
    /**
     * @see Zend_Http_Client::request()
     */
    public function request($method = null)
    {   
        Mage::helper('foomanjirafe')->debug('--------------------------------------REQUEST--------------------------------------');
        Mage::helper('foomanjirafe')->debug($this->getUri(true));
        $response = parent::request($method);
        Mage::helper('foomanjirafe')->debug($this->getLastRequest());
        Mage::helper('foomanjirafe')->debug('--------------------------------------RESPONSE--------------------------------------');
        Mage::helper('foomanjirafe')->debug($this->getLastResponse());        
        return $response;
    }

}
