<?php
/**
* Origo - social client
* ApiController
*
* @copyright Copyright (c) 2008-2009 Mario Volke
* @author    Mario Volke <mario.volke@webholics.de>
*/

class ApiController extends BaseController
{
	public function init()
	{
		parent::init();
		$this->_helper->viewRenderer->setNoRender();
	}

	public function preDispatch() 
	{	
		// force authentication for all api methods
		// except for error
		$request = $this->getRequest();
		if($request->getControllerName() != 'api' || $request->getActionName() != 'error') {
			if(!$this->authenticate()) {
				$this->_forward('error', null, null, array(
					'http_code' => 401,
					'code' => 'AuthenticationFailed',
					'message' => 'Authentication failed! Please check your credentials.'
				));
			}
		}
	}

	/**
	 * Error Action
	 */
	public function errorAction()
	{
		$code = $this->getRequest()->getParam('code');
		$http_code = $this->getRequest()->getParam('http_code');
		$message = $this->getRequest()->getParam('message');

		if(!empty($http_code))
			$this->getResponse()->setHttpResponseCode($http_code);
		
		$xml = 
			'<error>' .
				'<error_code>' . $code . '</error_code>' .
				'<error_message>' . $message . '</error_message>' .
			'</error>';

		$this->outputXml($xml);
	}

	/**
	 * Test Action
	 * Use this action to test authentication and status of the api.
	 */
	public function testAction()
	{
		$this->outputXml('
			<request>
				<code>200</code>
				<message>API status: OK</message>
			</request>
		');
	}

	/**
	 * Escape values for triple store queries.
	 *
	 * @param string $value The value to escape.
	 * @param string $type (uri|literal)
	 * @return string
	 */
	protected function escape($value, $type='literal') 
	{
		switch($type) {
			case 'uri':
				$value = str_replace(array('<', '>'), array('%3C', '%3E'), $value);
			default:
				$value = str_replace('"', '""', $value);
		}

		return $value;
	}

	/**
	 * Output XML
	 * and set correct header.
	 * 
	 * @param string|SimpleXMLElement $xml 
	 * @return void
	 */
	protected function outputXml($xml)
	{
		$response = $this->getResponse();
		$response->setHeader('Content-Type', 'application/xml');

		if($xml instanceof SimpleXMLElement)
			$response->appendBody($xml->asXML());
		else {
			$xml = trim($xml);
			if(substr($xml, 0, 5) != '<?xml')
				$xml = '<?xml version="1.0" encoding="UTF-8" ?>' . $xml;

			$response->appendBody($xml);
		}
	}
	
	/**
	 * Authentication
	 * 
	 * @return bool True if user is authenticated, false otherwise.
	 */
	protected function authenticate()
	{
		$config = $this->getConfig();
		$auth_config = array(
			'accept_schemes' 	=> 'basic',
			'realm' 			=> 'origo api',
		);

		$adapter = new Zend_Auth_Adapter_Http($auth_config);
		
		$basicResolver = new Zend_Auth_Adapter_Http_Resolver_Single(
			$config->api->auth->username,
			'origo api',
			$config->api->auth->password,
			'basic'
		);

		$adapter->setBasicResolver($basicResolver);

		$request = $this->getRequest();
		$response = $this->getResponse();

		assert($request instanceof Zend_Controller_Request_Http);
		assert($response instanceof Zend_Controller_Response_Http);

		$adapter->setRequest($request);
		$adapter->setResponse($response);

		$result = $adapter->authenticate();
		if($result->isValid())
			return true;

		return false;
	}
}
