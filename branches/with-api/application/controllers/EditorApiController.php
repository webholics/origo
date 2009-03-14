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
			$params = $this->_properties;

		$xml = '<result>';

		foreach($params as $key => $value) {
			if(isset($this->_properties[$key])) {
					
				$query = $this->_queryPrefix .
					'SELECT ?val WHERE {' .
						'<' . $identifier . '> <' . $this->_properties[$key][0] . '> ?val .' .
					'}';
				$row = $store->query($query, 'row');

				if(!$store->getErrors() && $row) {
					// some properties need special attention
					if($key == 'mbox')
						$row['val'] = substr($row['val'], 7);
				
					$xml .= '<property name="' . $key . '">' . $row['val'] . '</property>';
				}
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
			if(isset($this->_properties[$key])) {

				if(empty($value)) {
					$xml .= '<error>Given value is empty.</error>';
				}
				else {
					// delete old triple
					$store->query($this->_queryPrefix .
						'DELETE {' .
							'<' . $identifier . '> <' . $this->_properties[$key][0] . '> ?any .' .
						'}'
					);

					if($errors = $store->getErrors()) {
						$xml .= '<error>';
						for($i = 0; $i < count($errors); $i++) {
							$xml .= $errors[$i];
							if($i < count($errors)-1)
								$xml .= ' ';
						}
						$xml .= '</error>';
					}
					else {
						// insert new triple
						$query = $this->_queryPrefix . 
							'INSERT INTO <' . $identifier . '> {' .
								'<' . $identifier . '> ' . $this->_properties[$key][0] . ' ';

						// some properties need special attention
						if($key == 'mbox') {
							$value = 'mailto:' . trim($value);
						}
						else if($key == 'mbox_sha1sum') {
							$value = sha1('mailto:' . trim($value));
						}

						switch($this->_properties[$key][1]) {
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
								$xml .= $errors[$i];
								if($i < count($errors)-1)
									$xml .= ' ';
							}
							$xml .= '</error>';
						}
						else {
							$updated = true;
							$xml .= '<property name="' . $key . '">';
							$xml .= $value;
							$xml .= '</property>';
						}
					}
				}
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
			if(isset($this->_properties[$key])) {
				// delete triple
				$result = $store->query($this->_queryPrefix .
					'DELETE {' .
						'<' . $identifier . '> <' . $this->_properties[$key][0] . '> ?any .' .
					'}'
				);

				$xml .= '<param name="' . $key . '">';

				if($errors = $store->getErrors()) {
					$xml .= '<error>';
					for($i = 0; $i < count($errors); $i++) {
						$xml .= $errors[$i];
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
				$xml .= $errors[$i];
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
		
		$query = $this->_queryPrefix .
			'SELECT ?label ?sameas ?seealso WHERE {' .
				'<' . $identifier . '> owl:sameAs ?sameas . ' .
				'?sameas rdfs:seeAlso ?seealso .' .
				'OPTIONAL { ?sameas rdfs:label ?label . }' .
			'}';
		$rows = $store->query($query, 'rows');
		if($errors = $store->getErrors()) {
			$xml .= '<error>';
			for($i = 0; $i < count($errors); $i++) {
				$xml .= $errors[$i];
				if($i < count($errors)-1)
					$xml .= ' ';
			}
			$xml .= '</error>';
		}
		else {
			foreach($rows as $row) {
				$xml .= '<profile sameas="' . $row['sameas'] . '" seealso="' . $row['seealso'] . '"';

				if(isset($row['label']) && !empty($row['label']))
					$xml .= '>' . $row['label'] . '</profile>';
				else
					$xml .= '/>';
			}
		}

		$xml .= '</result>';

		$this->outputXml($xml);
		
	}

	/**
	 * Profiles update action
	 * Insert or update external profiles.
	 * Only GET params.
	 * Needs params seealso, sameas.
	 */
	public function profilesupdateAction()
	{
		$xml = '<result>';
		
		$params = $this->getRequest()->getQuery();
		if(!isset($params['seealso']) ||
			empty($params['seealso']) ||
			!isset($params['sameas']) ||
			empty($params['sameas'])) {
			
			$xml .= '<error>Not all parameters given. This method needs: seealso, sameas</error>';
		}
		else {
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
				$xml .= '<error>';
				for($i = 0; $i < count($errors); $i++) {
					$xml .= $errors[$i];
					if($i < count($errors)-1)
						$xml .= ' ';
				}
				$xml .= '</error>';
			}
			else {
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
					$xml .= '<error>';
					for($i = 0; $i < count($errors); $i++) {
						$xml .= $errors[$i];
						if($i < count($errors)-1)
							$xml .= ' ';
					}
					$xml .= '</error>';
				}
				else {
					$xml .= '<profile sameas="' . $sameas . '" seealso="' . $seealso .'"';
					if(empty($label)) 
						$xml .= '/>';
					else
						$xml .= '>' . $label . '</profile>';

					// clean profile cache
					$cache = $this->getCache();
					$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
				}
			}
		}

		$xml .= '</result>';

		$this->outputXml($xml);
	}

	/**
	 * Profiles delete action
	 * Delete external profiles.
	 * Only GET params.
	 * Needs param: sameas.
	 */
	public function profilesdeleteAction()
	{
		$xml = '<result>';
		
		$params = $this->getRequest()->getQuery();
		if(!isset($params['sameas']) ||
			empty($params['sameas'])) {
			
			$xml .= '<error>Not all parameters given. This method needs: sameas</error>';
		}
		else {
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
				$xml .= '<error>';
				for($i = 0; $i < count($errors); $i++) {
					$xml .= $errors[$i];
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

	/**
	 * Relationships get action
	 */
	public function relationshipsgetAction()
	{
		$identifier = $this->getIdentifier();
		$store = $this->getProfileStore();

		$xml = '<result>';
		
		$query = $this->_queryPrefix .
			'SELECT ?p ?name WHERE {' .
				'<' . $identifier . '> ?p ?name . ' .
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
			$xml .= '<error>';
			for($i = 0; $i < count($errors); $i++) {
				$xml .= $errors[$i];
				if($i < count($errors)-1)
					$xml .= ' ';
			}
			$xml .= '</error>';
		}
		else {
			$rels = array();
			foreach($rows as $row) {
				if(isset($rels[$row['name']])) 
					$rels[$row['name']] .= ',';
				else
					$rels[$row['name']] = '';

				$rels[$row['name']] .= array_search($row['p'], $this->_relationships);
			}

			foreach($rels as $to => $rel) {
				$xml .= '<relationship to="' . $to . '"';
				
				$query2 = $this->_queryPrefix .
					'SELECT ?seealso WHERE {' .
						'<' . $to . '> rdfs:seeAlso ?seealso .' .
					'}';
				if($row = $store->query($query2, 'row')) {
					$xml .= ' seealso="' . $row['seealso'] . '"';
				}
				
				$xml .= '>' . $rel . '</relationship>';
			}
		}

		$xml .= '</result>';

		$this->outputXml($xml);
	}

	/**
	 * Relationships update action
	 * Only GET params.
	 * Needs param: to.
	 */
	public function relationshipsupdateAction()
	{
		$xml = '<result>';
		
		$params = $this->getRequest()->getQuery();
		if(!isset($params['to']) || empty($params['to'])) {
			$xml .= '<error>Not all parameters given. This method needs: to</error>';
		}
		else {
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
				$xml .= '<error>';
				for($i = 0; $i < count($errors); $i++) {
					$xml .= $errors[$i];
					if($i < count($errors)-1)
						$xml .= ' ';
				}
				$xml .= '</error>';
			}
			else {
				$query = $this->_queryPrefix .
					'INSERT INTO <' . $identifier . '> {' .
						'<' . $identifier . '> foaf:knows <' . $to . '> .';

				if(isset($params['seealso']) && !empty($params['seealso']))
					$query .= '<' . $to . '> rdfs:seeAlso <' . $this->escape($params['seealso'], 'uri') . '> .';

				// add relationships
				$rels = 'knows';
				foreach($params as $key => $value) {
					if($key != 'knows' && 
						$key != 'seealso' && 
						$key != 'to' &&
						isset($this->_relationships[$key])) {

						$rels .= ',' . $key;

						$query .= '<' . $identifier . '> <' . $this->_relationships[$key] . '> <' . $to . '> .';
					}
				}
					
				$query .= '}';

				$store->query($query);
				if($errors = $store->getErrors()) {
					$xml .= '<error>';
					for($i = 0; $i < count($errors); $i++) {
						$xml .= $errors[$i];
						if($i < count($errors)-1)
							$xml .= ' ';
					}
					$xml .= '</error>';
				}
				else {
					$xml .= '<relationship to="' . $to . '"';

					if(isset($params['seealso']) && !empty($params['seealso']))
						$xml .= ' seealso="' . $params['seealso'] . '"';

					$xml .= '>' . $rels . '</relationship>';

					// clean profile cache
					$cache = $this->getCache();
					$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('profile'));
				}
			}
		}

		$xml .= '</result>';

		$this->outputXml($xml);
	}

	/**
	 * Relationships delete action
	 * Only GET params.
	 * Needs param: to.
	 */
	public function relationshipsdeleteAction()
	{
		$xml = '<result>';
		
		$params = $this->getRequest()->getQuery();
		if(!isset($params['to']) || empty($params['to'])) {
			$xml .= '<error>Not all parameters given. This method needs: to</error>';
		}
		else {
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
				$xml .= '<error>';
				for($i = 0; $i < count($errors); $i++) {
					$xml .= $errors[$i];
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
