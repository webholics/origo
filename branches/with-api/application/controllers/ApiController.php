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
	/**
	 * This prefix will be prepended to all sparql queries.
	 */
	protected $_queryPrefix = '
		PREFIX foaf: <http://xmlns.com/foaf/0.1/>
		PREFIX rel: <http://purl.org/vocab/relationship/>
	';

	/**
	 * Only the properties in this array are allowed to be set via the api
	 */
	protected $_properties = array(
		'title' 				=> array('http://xmlns.com/foaf/0.1/title', 			'literal'),
		'nick' 					=> array('http://xmlns.com/foaf/0.1/nick', 				'literal'),
		'homepage' 				=> array('http://xmlns.com/foaf/0.1/homepage',			'uri'),
		'mbox' 					=> array('http://xmlns.com/foaf/0.1/mbox', 				'uri'),
		'mbox_sha1sum' 			=> array('http://xmlns.com/foaf/0.1/mbox_sha1sum', 		'literal'),
		'img' 					=> array('http://xmlns.com/foaf/0.1/img', 				'uri'),
		'family_name' 			=> array('http://xmlns.com/foaf/0.1/family_name', 		'literal'),
		'givenname' 			=> array('http://xmlns.com/foaf/0.1/givenname', 		'literal'),
		'weblog' 				=> array('http://xmlns.com/foaf/0.1/weblog', 			'uri'),
		'workinfohomepage' 		=> array('http://xmlns.com/foaf/0.1/workInfoHomepage', 	'uri'),
		'workplacehomepage' 	=> array('http://xmlns.com/foaf/0.1/workplaceHomepage',	'uri'),
		'plan' 					=> array('http://xmlns.com/foaf/0.1/plan', 				'uri'),
		'geekcode' 				=> array('http://xmlns.com/foaf/0.1/geekcode', 			'literal'),
		'gender' 				=> array('http://xmlns.com/foaf/0.1/gender', 			'literal'),
		'myersbriggs' 			=> array('http://xmlns.com/foaf/0.1/myersBriggs', 		'literal'),
		'openid' 				=> array('http://xmlns.com/foaf/0.1/openid', 			'uri'),
		'icq' 					=> array('http://xmlns.com/foaf/0.1/icqChatID', 		'literal'),
		'msn' 					=> array('http://xmlns.com/foaf/0.1/msnChatID', 		'literal'),
		'aim' 					=> array('http://xmlns.com/foaf/0.1/aimChatID', 		'literal'),
		'yahoo' 				=> array('http://xmlns.com/foaf/0.1/yahooChatID', 		'literal'),
		'jabber' 				=> array('http://xmlns.com/foaf/0.1/jabberID', 			'literal'),
	);

	/**
	 * Possible relationships
	 */
	protected $_relationships = array(
		'knows' => 'http://xmlns.com/foaf/0.1/knows',
		'acquaintanceof' => 'http://purl.org/vocab/relationship/acquaintanceOf',
		'ambivalentof' => 'http://purl.org/vocab/relationship/ambivalentOf',
		'ancestorof' => 'http://purl.org/vocab/relationship/ancestorOf',
		'antagonistof' => 'http://purl.org/vocab/relationship/antagonistOf',
		'apprenticeto' => 'http://purl.org/vocab/relationship/apprenticeTo',
		'childof' => 'http://purl.org/vocab/relationship/childOf',
		'closefriendof' => 'http://purl.org/vocab/relationship/closeFriendOf',
		'collaborateswith' => 'http://purl.org/vocab/relationship/collaboratesWith',
		'colleagueof' => 'http://purl.org/vocab/relationship/colleagueOf',	
		'descendantof' => 'http://purl.org/vocab/relationship/descendantOf',
		'employedby' => 'http://purl.org/vocab/relationship/employedBy',	
		'employerof' => 'http://purl.org/vocab/relationship/employerOf',
		'enemyof' => 'http://purl.org/vocab/relationship/enemyOf',
		'engagedto' => 'http://purl.org/vocab/relationship/engagedTo',	
		'friendof' => 'http://purl.org/vocab/relationship/friendOf',	
		'grandchildof' => 'http://purl.org/vocab/relationship/grandchildOf',
		'grandparentof' => 'http://purl.org/vocab/relationship/grandparentOf',
		'hasmet' => 'http://purl.org/vocab/relationship/hasMet',
		'knowsbyreputation' => 'http://purl.org/vocab/relationship/knowsByReputation',
		'knowsinpassing' => 'http://purl.org/vocab/relationship/knowsInPassing',
		'knowsof' => 'http://purl.org/vocab/relationship/knowsOf',
		'lifepartnerof' => 'http://purl.org/vocab/relationship/lifePartnerOf',
		'liveswith' => 'http://purl.org/vocab/relationship/livesWith',	
		'lostcontactwith' => 'http://purl.org/vocab/relationship/lostContactWith',
		'mentorof' => 'http://purl.org/vocab/relationship/mentorOf',	
		'neighborof' => 'http://purl.org/vocab/relationship/neighborOf',	
		'parentof' => 'http://purl.org/vocab/relationship/parentOf',	
		'participant' => 'http://purl.org/vocab/relationship/participant',	
		'participantin' => 'http://purl.org/vocab/relationship/participantIn',
		'siblingof' => 'http://purl.org/vocab/relationship/siblingOf',	
		'spouseof' => 'http://purl.org/vocab/relationship/spouseOf',	
		'workswith' => 'http://purl.org/vocab/relationship/worksWith',	
		'wouldliketoknow' => 'http://purl.org/vocab/relationship/wouldLikeToKnow',
	);	

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
