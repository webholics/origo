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
	 * The mapping has to be bijective.
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
		'icq' 					=> 'foaf:icqChatID',
		'msn' 					=> 'foaf:msnChatID',
		'aim' 					=> 'foaf:aimChatID',
		'yahoo' 				=> 'foaf:yahooChatID',
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
		'foaf:mbox_sha1sum' => 'literal',
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
	 * Get action
	 * Get the unique triples of the profile.
	 * Only GET params.
	 * If no parameter is provided the whole profile will be returned.
	 */
	public function getAction()
	{
		$params = $this->getRequest()->getQuery();

		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		// if no params are provided 
		// we return the whole profile
		if(count($params) == 0)
			$params = $this->predicateShortcuts;

		$xml = '<result>';

		foreach($params as $key => $value) {
			// resolve shortcuts
			if(isset($this->predicateShortcuts[strtolower($key)]))
				$resKey = $this->predicateShortcuts[strtolower($key)];
			else
				$resKey = $key;

			if(isset($this->uniquePredicates[$resKey])) {
				
				$query = $this->queryPrefix .
					'SELECT ?val WHERE {' .
						'<' . $identifier . '> ' . $resKey . ' ?val .' .
					'}';
				$row = $store->query($query, 'row');

				if(!$store->getErrors() && $row)
					$xml .= '<param name="' . $key . '">' . $row['val'] . '</param>';
			}
		}

		$xml .= '</result>';

		$this->outputXml($xml);
	}
	
	/**
	 * Update action
	 * Only GET params.
	 * Update the triples that exist exactly once per profile.
	 */
	public function updateAction()
	{
		$params = $this->getRequest()->getQuery();
		
		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		$xml = '<result>';

		$updated = false;
		foreach($params as $key => $value) {
			// resolve shortcuts
			if(isset($this->predicateShortcuts[strtolower($key)]))
				$resKey = $this->predicateShortcuts[strtolower($key)];
			else
				$resKey = $key;

			if(isset($this->uniquePredicates[$resKey])) {

				$xml .= '<param name="' . $key . '">';

				if(empty($value)) {
					$xml .= '<error>Given value is empty.</error>';
				}
				else {
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

						// some predicates need special attention
						if($resKey == 'foaf:mbox') {
							$value = 'mailto:' . trim($value);
						}
						else if($resKey == 'foaf:mbox_sha1sum') {
							$value = sha1('mailto:' . trim($value));
						}

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
	
	/**
	 * Delete action
	 * Only GET params.
	 * Delete triples that exist exactly once per profile.
	 */
	public function deleteAction()
	{
		$params = $this->getRequest()->getQuery();
		
		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		$xml = '<result>';

		$updated = false;
		foreach($params as $key => $value) {
			// resolve shortcuts
			if(isset($this->predicateShortcuts[strtolower($key)]))
				$resKey = $this->predicateShortcuts[strtolower($key)];
			else
				$resKey = $key;

			if(isset($this->uniquePredicates[$resKey])) {
				// delete triple
				$result = $store->query($this->queryPrefix .
					'DELETE {' .
						'<' . $identifier . '> ' . $resKey . ' ?any .' .
					'}'
				);

				$xml .= '<param name="' . $key . '">';

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
					$removed_triples = $result['result']['t_count'];
					if($removed_triples > 0)
						$updated = true;
					$xml .= $removed_triples;
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

	/**
	 * Clean action.
	 * Deletes the whole profile.
	 */
	public function cleanAction()
	{
		$xml = '<result>';

		$store = $this->getProfileStore();
		$store->reset();
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
			$xml .= 1;
			
			// clean profile cache
			$cache = $this->getCache();
			$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
		}

		$xml .= '</result>';

		$this->outputXml($xml);
	}

	/**
	 * Profiles get action
	 * Get external profiles.
	 */
	public function profilesgetAction()
	{
		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		$xml = '<result>';
		
		$query = $this->queryPrefix .
			'SELECT ?label ?sameas ?seealso WHERE {' .
				'<' . $identifier . '> owl:sameAs ?sameas . ' .
				'?sameas rdfs:seeAlso ?seealso ;' .
					'rdfs:label ?label .' .
			'}';
		$rows = $store->query($query, 'rows');
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
			foreach($rows as $row) {
				$xml .= 
					'<profile label="' . $row['label'] . '">' .
						'<sameas>' . $row['sameas'] . '</sameas>' .
						'<seealso>' . $row['seealso'] . '</seealso>' .
					'</profile>';
			}
		}

		$xml .= '</result>';

		$this->outputXml($xml);
		
	}

	/**
	 * Profiles insert action
	 * Insert external profiles.
	 * Only GET params.
	 * Needs params label, seealso, sameas.
	 */
	public function profilesinsertAction()
	{
		$xml = '<result>';
		
		$params = $this->getRequest()->getQuery();
		if(!isset($params['label']) ||
			empty($params['label']) ||
			!isset($params['seealso']) ||
			empty($params['seealso']) ||
			!isset($params['sameas']) ||
			empty($params['sameas'])) {
			
			$xml .= '<error>Not all parameters given. This method needs: label, seealso, sameas</error>';
		}
		else {
			$identifier = $this->getIdentifier();
			$store = $this->getProfileStore();
			$sameas = $this->escape($params['sameas'], 'uri');
			$seealso = $this->escape($params['seealso'], 'uri');
			$label = $this->escape($params['label'], 'literal');
			
			$query = $this->queryPrefix .
				'INSERT INTO <' . $identifier . '> {' .
					'<' . $identifier . '> owl:sameAs <' . $sameas . '> .' .
					'<' . $sameas . '> rdfs:seeAlso <' . $seealso . '> .' .
					'<' . $sameas . '> rdfs:label "' . $label . '" . ' .
				'}';
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
				$xml .= 
					'<profile label="' . $label . '">' .
						'<sameas>' . $sameas . '</sameas>' .
						'<seealso>' . $seealso . '</seealso>' .
					'</profile>';

				// clean profile cache
				$cache = $this->getCache();
				$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
			}
		}

		$xml .= '</result>';

		$this->outputXml($xml);
	}

	/**
	 * Profiles delete action
	 * Delete external profiles.
	 * Only GET params.
	 * Needs params label, seealso, sameas.
	 */
	public function profilesdeleteAction()
	{
		$xml = '<result>';
		
		$params = $this->getRequest()->getQuery();
		if(!isset($params['label']) ||
			empty($params['label']) ||
			!isset($params['seealso']) ||
			empty($params['seealso']) ||
			!isset($params['sameas']) ||
			empty($params['sameas'])) {
			
			$xml .= '<error>Not all parameters given. This method needs: label, seealso, sameas</error>';
		}
		else {
			$identifier = $this->getIdentifier();
			$store = $this->getProfileStore();
			$sameas = $this->escape($params['sameas'], 'uri');
			$seealso = $this->escape($params['seealso'], 'uri');
			$label = $this->escape($params['label'], 'literal');
			
			$query = $this->queryPrefix .
				'DELETE {' .
					'<' . $identifier . '> owl:sameAs <' . $sameas . '> .' .
					'<' . $sameas . '> rdfs:seeAlso <' . $seealso . '> .' .
					'<' . $sameas . '> rdfs:label "' . $label . '" . ' .
				'}';
			$result = $store->query($query);
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
				if($result['result']['t_count'] > 0) {
					$xml .= 1;
					
					// clean profile cache
					$cache = $this->getCache();
					$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
				}
				else
					$xml .= 0;
			}
		}

		$xml .= '</result>';

		$this->outputXml($xml);
	}
}
