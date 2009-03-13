<?php
/**
* Origo - social client
* EditorApiController
*
* @copyright Copyright (c) 2008-2009 Mario Volke
* @author    Mario Volke <mario.volke@webholics.de>
*/

class EditorApiController extends ApiController
{
	/**
	 * This prefix will be prepended to all sparql queries.
	 */
	protected $queryPrefix = '
		PREFIX foaf: <http://xmlns.com/foaf/0.1/>
	';

	/**
	 * Those shortcuts can be used to abstract more from the ontology we use.
	 * All shortcuts are not case-sensitve.
	 */
	protected $predicateShortcuts = array(
		'title' 				=> 'foaf:title',
		'nick' 					=> 'foaf:nick',
		'homepage' 				=> 'foaf:homepage',
		'mbox' 					=> 'foaf:mbox',
		'mbox_sha1sum' 			=> 'foaf:mbox_sha1sum',
		'img' 					=> 'foaf:img',
		'family_name' 			=> 'foaf:family_name',
		'givenname' 			=> 'foaf:givenname',
		'weblog' 				=> 'foaf:weblog',
		'workinfohomepage' 		=> 'foaf:workInfoHomepage',
		'workplacehomepage' 	=> 'foaf:workplaceHomepage',
		'plan' 					=> 'foaf:plan',
		'geekcode' 				=> 'foaf:geekcode',
		'gender' 				=> 'foaf:gender',
		'myersbriggs' 			=> 'foaf:myersBriggs',
		'openid' 				=> 'foaf:openid',
		'icqchatid' 			=> 'foaf:icqChatID',
		'icq' 					=> 'foaf:icqChatID',
		'msnchatid' 			=> 'foaf:msnChatID',
		'msn' 					=> 'foaf:msnChatID',
		'aimchatid' 			=> 'foaf:aimChatID',
		'aim' 					=> 'foaf:aimChatID',
		'yahoochatid' 			=> 'foaf:yahooChatID',
		'yahoo' 				=> 'foaf:yahooChatID',
		'jabberid' 				=> 'foaf:jabberID',
		'jabber' 				=> 'foaf:jabberID'
	);
	
	/**
	 * Only the predicates in this array are allowed to be set via the api
	 */
	protected $uniquePredicates = array(
		'foaf:title' => 'literal', 
		'foaf:nick' => 'literal', 
		'foaf:homepage' => 'uri', 
		'foaf:mbox' => 'uri', 
		'foaf:mbox_sha1sum' => 'uri',
		'foaf:img' => 'uri', 
		'foaf:family_name' => 'literal', 
		'foaf:givenname' => 'literal', 
		'foaf:weblog' => 'uri', 
		'foaf:workInfoHomepage' => 'uri',
		'foaf:workplaceHomepage' => 'uri', 
		'foaf:plan' => 'uri', 
		'foaf:geekcode' => 'literal', 
		'foaf:gender' => 'literal',
		'foaf:myersBriggs' => 'literal', 
		'foaf:openid' => 'uri', 
		'foaf:icqChatID' => 'literal', 
		'foaf:msnChatID' => 'literal', 
		'foaf:aimChatID' => 'literal', 
		'foaf:yahooChatID' => 'uri', 
		'foaf:jabberID' => 'uri'
	);
	
	/**
	 * Update action
	 * Update the triples that exist exactly once per profile.
	 */
	public function updateAction()
	{
		$params = $this->getRequest()->getParams();
		
		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		$xml = '<result>';

		// resolve params
		$update = array();
		$updated = false;
		foreach($params as $key => $value) {
			// resolve shortcuts
			if(isset($this->predicateShortcuts[strtolower($key)]))
				$resKey = $this->predicateShortcuts[strtolower($key)];
			else
				$resKey = $key;

			if(isset($this->uniquePredicates[$resKey])) {

				$xml .= '<param name="' . $key . '">';
				
				// delete old triple
				$store->query($this->queryPrefix .
					'DELETE {' .
						'<' . $identifier . '> ' . $resKey . ' ?any .' .
					'}'
				);

				if($errors = $store->getErrors()) {
					$xml .= '<error>';
					for($i = 0; $i < count($errors); $i++) {
						$xml .= $err;
						if($i < count($errors)-1)
							$xml .= ' ';
					}
					$xml .= '</error>';
				}
				else {
					// insert new triple
					$query = $this->queryPrefix . 
						'INSERT INTO <' . $identifier . '> {' .
							'<' . $identifier . '> ' . $resKey . ' ';

					switch($this->uniquePredicates[$resKey]) {
						case 'uri':
							$query .= '<' . $this->escape($value, 'uri') . '>';
							break;
						case 'literal':
							$query .= '"' . $this->escape($value, 'literal') . '"';
							break;
					}

					$query .= ' . }';
					
					$store->query($query);

					if($errors = $store->getErrors()) {
						$xml .= '<error>';
						for($i = 0; $i < count($errors); $i++) {
							$xml .= $err;
							if($i < count($errors)-1)
								$xml .= ' ';
						}
						$xml .= '</error>';
					}
					else {
						$updated = true;
						$xml .= $value;
					}
				}

				$xml .= '</param>';
			}
		}

		$xml .= '</result>';

		$this->outputXml($xml);

		// clean profile cache
		if($updated) {
			$cache = $this->getCache();
			$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
		}
	}
}
