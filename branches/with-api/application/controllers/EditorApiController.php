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
	 * Get action
	 * Get the unique triples of the profile.
	 */
	public function getAction()
	{
		$params = $this->getRequest()->getPost();

		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		// if no params are provided 
		// we return the whole profile
		if(count($params) == 0)
			$params = $this->_properties;

		$profile = $this->getProfile($identifier, $store);
		if($profile === false)
			return;

		$this->outputXml('<result>' . $profile . '</result>');
	}
	
	/**
	 * Update action
	 * Only POST params.
	 * Update the triples that exist exactly once per profile.
	 */
	public function updateAction()
	{
		$params = $this->getRequest()->getPost();
		
		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		$deleteQuery = $this->_queryPrefix .
			'DELETE {';
		$insertQuery = $this->_queryPrefix .
			'INSERT INTO <' . $identifier . '> {';
		$count = 1;
		$update = false;
		foreach($params as $key => $value) {
			if(isset($this->_properties[$key])) {

				if(empty($value)) {
					$this->_forward('error', 'api', null, array(
						'code' => 'ValidationError',
						'message' => 'Given value is empty for ' . $key . '.'
					));
					return;
				}
				
				$update = true;
					
				$deleteQuery .= '<' . $identifier . '> <' . $this->_properties[$key][0] . '> ?any' . $count . ' .';
				$insertQuery .= '<' . $identifier . '> <' . $this->_properties[$key][0] . '> ';
				$count++;
					
				// some properties need special attention
				if($key == 'mbox') {
					$value = 'mailto:' . trim($value);
				}
				else if($key == 'mbox_sha1sum') {
					$value = sha1('mailto:' . trim($value));
				}

				switch($this->_properties[$key][1]) {
					case 'uri':
						$insertQuery .= '<' . $this->escape($value, 'uri') . '>';
						break;
					case 'literal':
						$insertQuery .= '"' . $this->escape($value, 'literal') . '"';
						break;
				}
				
				$insertQuery .= ' .';
			}
		}
			
		if($update) {
			$deleteQuery .= '}';
			$insertQuery .= '}';
			
			// delete old triples
			$store->query($deleteQuery);
			if($errors = $store->getErrors()) {
				$this->forwardTripleStoreError($errors);
				return;
			}

			// insert new triples
			$store->query($insertQuery);
			if($errors = $store->getErrors()) {
				$this->forwardTripleStoreError($errors);
				return;
			}
		}
		
		$profile = $this->getProfile($identifier, $store);
		if($profile === false)
			return;
		
		$this->outputXml('<result>' . $profile . '</result>');

		// clean profile cache
		$cache = $this->getCache();
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
	}
	
	/**
	 * Delete action
	 * Only POST params.
	 * Delete triples that exist exactly once per profile.
	 */
	public function deleteAction()
	{
		$params = $this->getRequest()->getPost();
		
		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		$deleteQuery = $this->_queryPrefix .
			'DELETE {';
		$count = 1;
		foreach($params as $key => $value) {
			if(isset($this->_properties[$key])) {
				$deleteQuery .= '<' . $identifier . '> <' . $this->_properties[$key][0] . '> ?any' . $count . ' .';
				$count++;
			}
		}
			
		$deleteQuery .= '}';
		
		// delete triples
		$store->query($deleteQuery);
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return;
		}

		$profile = $this->getProfile($identifier, $store);
		if($profile === false)
			return;

		$this->outputXml('<result>' . $profile . '</result>');

		// clean profile cache
		$cache = $this->getCache();
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
	}

	/**
	 * Clean action.
	 * Deletes the whole profile.
	 */
	public function cleanAction()
	{
		$store = $this->getProfileStore();
		$store->reset();
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return;
		}

		// clean profile cache
		$cache = $this->getCache();
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));

		$this->outputXml('<result>1</result>');
	}

	/**
	 * Profiles get action
	 * Get external profiles.
	 */
	public function profilesgetAction()
	{
		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		$profiles = $this->getProfiles($identifier, $store);
		if($profiles === false)
			return;

		$this->outputXml('<result>' . $profiles . '</result>');
	}

	/**
	 * Profiles update action
	 * Insert or update external profiles.
	 * Only POST params.
	 * Needs params seealso, sameas.
	 */
	public function profilesupdateAction()
	{
		$params = $this->getRequest()->getPost();
		if(!isset($params['seealso']) ||
			empty($params['seealso']) ||
			!isset($params['sameas']) ||
			empty($params['sameas'])) {
			
			$this->_forward('error', 'api', null, array(
				'code' => 'RequiredError',
				'message' => 'Not all parameters given. This method requires: seealso, sameas.'
			));
			return;
		}

		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();
		$sameas = $this->escape($params['sameas'], 'uri');
		$seealso = $this->escape($params['seealso'], 'uri');

		// delete old triples
		$query = $this->_queryPrefix .
			'DELETE {' .
				'<' . $identifier . '> owl:sameAs <' . $sameas . '> .' .
				'<' . $sameas . '> ?any1 ?any2 .' .
			'}';
		$result = $store->query($query);
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return;
		}

		$query = $this->_queryPrefix .
			'INSERT INTO <' . $identifier . '> {' .
				'<' . $identifier . '> owl:sameAs <' . $sameas . '> .' .
				'<' . $sameas . '> rdfs:seeAlso <' . $seealso . '> .';

		if(isset($params['label']) && !empty($params['label'])) {
			$label = $params['label'];
			$query .= '<' . $sameas . '> rdfs:label "' . $this->escape($params['label'], 'literal') . '" . ';
		}
		else
			$label = '';
			
		$query .= '}';

		$store->query($query);
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return;
		}

		// clean profile cache
		$cache = $this->getCache();
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
			
		$profiles = $this->getProfiles($identifier, $store);
		if($profiles === false)
			return;

		$this->outputXml('<result>' . $profiles . '</result>');
	}

	/**
	 * Profiles delete action
	 * Delete external profiles.
	 * Only POST params.
	 * Needs param: sameas.
	 */
	public function profilesdeleteAction()
	{
		$params = $this->getRequest()->getPost();
		if(!isset($params['sameas']) || empty($params['sameas'])) {
			$this->_forward('error', 'api', null, array(
				'code' => 'RequiredError',
				'message' => 'Not all parameters given. This method requires: sameas.'
			));
			return;
		}

		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();
		$sameas = $this->escape($params['sameas'], 'uri');
		
		$query = $this->_queryPrefix .
			'DELETE {' .
				'<' . $identifier . '> owl:sameAs <' . $sameas . '> .' .
				'<' . $sameas . '> ?any1 ?any2 .' .
			'}';
		$result = $store->query($query);
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return;
		}
		
		if($result['result']['t_count'] > 0) {
			// clean profile cache
			$cache = $this->getCache();
			$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
		}

		$profiles = $this->getProfiles($identifier, $store);
		if($profiles === false)
			return;

		$this->outputXml('<result>' . $profiles . '</result>');
	}

	/**
	 * Relationships get action
	 */
	public function relationshipsgetAction()
	{
		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		$relationships = $this->getRelationships($identifier, $store);
		if($relationships === false)
			return;

		$this->outputXml('<result>' . $relationships . '</result>');
	}

	/**
	 * Relationships update action
	 * Only POST params.
	 * Needs param: to.
	 */
	public function relationshipsupdateAction()
	{
		$params = $this->getRequest()->getPost();
		if(!isset($params['to']) || empty($params['to'])) {
			$this->_forward('error', 'api', null, array(
				'code' => 'RequiredError',
				'message' => 'Not all parameters given. This method requires: to.'
			));
			return;
		}

		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();
		$to = $this->escape($params['to'], 'uri');

		// delete old triples
		$query = $this->_queryPrefix .
			'DELETE FROM {' .
				'<' . $identifier . '> ?any1 <' . $to . '> .' .
				'<' . $to . '> ?any2 ?any3 .' .
			'}';
		$store->query($query);
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return;
		}
		
		// try to load the provided profile
		$toId = $this->loadUri($to);
		if($toId)
			$to = $toId;

		$insertQuery = $this->_queryPrefix .
			'INSERT INTO <' . $identifier . '> {' .
				'<' . $identifier . '> foaf:knows <' . $to . '> .';
		
		// add relationships
		$rels = 'knows';
		foreach($params as $key => $value) {
			if($key != 'knows' && 
				$key != 'seealso' && 
				$key != 'to' &&
				isset($this->_relationships[$key])) {

				$rels .= ',' . $key;

				$insertQuery .= '<' . $identifier . '> <' . $this->_relationships[$key] . '> <' . $to . '> .';
			}
		}

		// store some basic values in our profile
		// in order to accelerate browsing
		$loadProperties = array('name', 'nick', 'img', 'depiction', 'family_name', 'givenname', 'mbox', 'mbox_sha1sum');
		if($toId) {
			$browserStore = $this->getBrowserStore();
			$query = $this->_queryPrefix .
				'SELECT ?type';
			foreach($loadProperties as $p)
				$query .= ' ?' . $p;
			$query .= ' WHERE {' .
				'<' . $to . '> rdf:type ?type . ';
			foreach($loadProperties as $p) {
				$query .= 'OPTIONAL { <' . $to . '> <' . $this->_properties[$p][0] . '> ?' . $p . ' . } ';
			}
			$query .= ' }';

			$row = $browserStore->query($query, 'row');
			if(!$store->getErrors() && $row) {
				foreach($loadProperties as $p) {
					if(isset($row[$p]) && !empty($row[$p])) {
						$insertQuery .= '<' . $to . '> <' . $this->_properties[$p][0] . '> ';
						switch($this->_properties[$p][1]) {
							case 'uri':
								$insertQuery .= '<' . $this->escape($row[$p], 'uri') . '>';
								break;
							case 'literal':
								$insertQuery .= '"' . $this->escape($row[$p], 'literal') . '"';
								break;
						}

						$insertQuery .= ' . ';
					}
				}
			}
		}
			
		$insertQuery .= '}';

		$store->query($insertQuery);
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return;
		}
		
		$relationships = $this->getRelationships($identifier, $store);
		if($relationships === false)
			return;

		// clean profile cache
		$cache = $this->getCache();
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));

		$this->outputXml('<result>' . $relationships . '</result>');
	}

	/**
	 * Relationships delete action
	 * Only POST params.
	 * Needs param: to.
	 */
	public function relationshipsdeleteAction()
	{
		$params = $this->getRequest()->getPost();
		if(!isset($params['to']) || empty($params['to'])) {
			$this->_forward('error', 'api', null, array(
				'code' => 'RequiredError',
				'message' => 'Not all parameters given. This method requires: to.'
			));
			return;
		}

		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();
		$to = $this->escape($params['to'], 'uri');
		
		$query = $this->_queryPrefix .
			'DELETE {' .
				'<' . $identifier . '> ?any1 <' . $to . '> .' .
				'<' . $to . '> ?any2 ?any3 .' .
			'}';

		$result = $store->query($query);
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return;
		}
		
		if($result['result']['t_count'] > 0) {
			// clean profile cache
			$cache = $this->getCache();
			$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
		}

		$relationships = $this->getRelationships($identifier, $store);
		if($relationships === false)
			return;

		$this->outputXml('<result>' . $relationships . '</result>');
	}

	/**
	 * Get relationships of $id.
	 *
	 * @param string $id bnode or uri identifier
	 * @param ARC2_Store $store The triple store to use
	 * @return string|false Relationships as XML or false if an error occured.
	 */
	protected function getRelationships($id, $store) 
	{
		$xml = '';

		$query = $this->_queryPrefix .
			'SELECT ?to ?p WHERE {' .
				'<' . $id . '> ?p ?to . ' .
				'FILTER(';

		$first = true;
		foreach($this->_relationships as $rel) {
			if($first)
				$first = false;
			else
				$query .= ' || ';
			$query .= ' ?p = <' . $rel . '> ';
		}
		$query .= ') }';
		$rows = $store->query($query, 'rows');
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return false;
		}

		$rels = array();
		foreach($rows as $row) {
			if(isset($rels[$row['to']])) 
				$rels[$row['to']] .= ',';
			else
				$rels[$row['to']] = '';

			$rels[$row['to']] .= array_search($row['p'], $this->_relationships);
		}

		foreach($rels as $to => $rel) {
			$profile = $this->getProfile($to, $store);
			if($profile === false)
				return false;
				
			$xml .= '<relationship type="' . $rel . '">';
			$xml .= $profile;
			$xml .= '</relationship>';
		}
	
		return $xml;
	}
}
