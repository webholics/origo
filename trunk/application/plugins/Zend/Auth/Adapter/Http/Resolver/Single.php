<?php
/**
 * @see Zend_Auth_Adapter_Http_Resolver_Interface
 */
require_once 'Zend/Auth/Adapter/Http/Resolver/Interface.php';


/**
 * HTTP Authentication Single Resolver
 *
 * @category   Zend
 * @package    Zend_Auth
 * @subpackage Zend_Auth_Adapter_Http
 * @copyright  Copyright (c) 2009 Mario Volke
 * @author     Mario Volke <mario.volke@webholics.de>
 */
class Zend_Auth_Adapter_Http_Resolver_Single implements Zend_Auth_Adapter_Http_Resolver_Interface
{
	protected $_username;
	protected $_realm;
	protected $_credentials;

	/**
	 * Constructor
	 *
	 * @param string $username Username
	 * @param string $realm Realm
	 * @param string $credentials Credentials
	 * @param string $scheme Http authentication scheme basic or digest
	 * @return void
	 */
	public function __construct($username, $realm, $credentials, $scheme='basic')
	{
		$this->_username = $username;
		$this->_realm = $realm;
		
		if($scheme == 'digest')
			$this->_credentials = md5($username . ':' . $realm . ':' . $credentials);
		else
			$this->_credentials = $credentials;
	}

    /**
     * Resolve username/realm to password/hash/etc.
     *
     * @param  string $username Username
     * @param  string $realm    Authentication Realm
     * @return string|false User's shared secret, if the user is found in the
     *         realm, false otherwise.
     */
    public function resolve($username, $realm)
	{
		if(empty($username)) {
			/**
			 * @see Zend_Auth_Adapter_Http_Resolver_Exception
			 */
			require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
			throw new Zend_Auth_Adapter_Http_Resolver_Exception('Username is required');
		} 
		else if(!ctype_print($username) || strpos($username, ':') !== false) {
			/**
			 * @see Zend_Auth_Adapter_Http_Resolver_Exception
			 */
			require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
			throw new Zend_Auth_Adapter_Http_Resolver_Exception('Username must consist only of printable characters, '
															  . 'excluding the colon');
        }
        if(empty($realm)) {
			/**
			 * @see Zend_Auth_Adapter_Http_Resolver_Exception
			 */
			require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
			throw new Zend_Auth_Adapter_Http_Resolver_Exception('Realm is required');
		} 
		else if (!ctype_print($realm) || strpos($realm, ':') !== false) {
			/**
			 * @see Zend_Auth_Adapter_Http_Resolver_Exception
			 */
			require_once 'Zend/Auth/Adapter/Http/Resolver/Exception.php';
			throw new Zend_Auth_Adapter_Http_Resolver_Exception('Realm must consist only of printable characters, '
															  . 'excluding the colon.');
		}

		if($this->_username == $username && $this->_realm == $realm)
			return $this->_credentials;

		return false;
	}
}
