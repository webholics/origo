<?php
/**
* Origo - social client
* BrowserApiController
*
* @copyright Copyright (c) 2008-2009 Mario Volke
* @author    Mario Volke <mario.volke@webholics.de>
*/

class BrowserApiController extends ApiController
{
	/**
	 * Max number of seeAlso properties to resolve and load.
	 */
	protected  $_maxSeeAlso = 10;

	/**
	 * Clean action.
	 * Deletes all data stored in browser triple store
	 * or if uri param is set only the data related to uri.
	 * Only POST params.
	 * Optional param: uri
	 */
	public function cleanAction()
	{
		$store = $this->getBrowserStore();

		$params = $this->getRequest()->getPost();
		if(!isset($params['uri']) || empty($params['uri'])) {
			$store->reset();
			if($errors = $store->getErrors()) {
				$this->forwardTripleStoreError($errors);
				return;
			}
		}
		else {
			$query = 'DELETE { <' . $this->escape($params['uri'], 'uri') . '> ?p ?o . }';
			$store->query($query);
			if($errors = $store->getErrors()) {
				$this->forwardTripleStoreError($errors);
				return;
			}
		}

		$this->outputXml('<result>1</result>');
	}

	/**
	 * Profile action
	 * Get a profile.
	 * Only POST params.
	 * Needs param: uri
	 */
	public function profileAction()
	{
		$store = $this->getBrowserStore();

		$params = $this->getRequest()->getPost();
		if(!isset($params['uri']) || empty($params['uri'])) {
			$this->_forward('error', 'api', null, array(
				'code' => 'RequiredError',
				'message' => 'Not all parameters given. This method requires: uri.'
			));
			return;
		}

		$uri = $this->escape($params['uri'], 'uri');
		if(!($id = $this->loadUri($uri))) {
			return;
		}

		$profile = $this->getProfile($id, $store);
		if($profile === false) 
			return;

		$this->outputXml('<result>' . $profile . '</result>');
	}
	
	/**
	 * Relationships action
	 * Get relationships limited to the ones provided by GET params
	 * or all relationships if no params provided.
	 * Only POST params.
	 * Needs param: uri
	 */
	public function relationshipsAction()
	{
		$params = $this->getRequest()->getPost();
		if(!isset($params['uri']) || empty($params['uri'])) {
			$this->_forward('error', 'api', null, array(
				'code' => 'RequiredError',
				'message' => 'Not all parameters given. This method requires: uri.'
			));
			return;
		}
		
		$uri = $this->escape($params['uri'], 'uri');
		if(!($id = $this->loadUri($uri))) {
			return;
		}

		$relationships = array();
		foreach($params as $key => $value) {
			if($key != 'uri' && isset($this->_relationships[$key]))
				$relationships[] = $key;
		}
		$relationships = array_unique($relationships);

		if(count($relationships) == 0)
			$relationships[] = 'knows';

		$query = $this->_queryPrefix .
			'SELECT ?to ?p ?label WHERE {' .
				'{ <' . $id . '> ?p ?to . }' .
				' UNION ' .
				'{ <' . $id . '> owl:sameAs ?sameas . ' .
				'?sameas ?p ?to . ' .
				'OPTIONAL { ?sameas rdfs:label ?label . } }' .
				'FILTER(';

		$first = true;
		foreach($relationships as $rel) {
			if($first)
				$first = false;
			else
				$query .= ' || ';
			$query .= ' ?p = <' . $this->_relationships[$rel] . '> ';
		}
		$query .= ') }';

		$store = $this->getBrowserStore();
		$rows = $store->query($query, 'rows');
		if($errors = $store->getErrors()) {
			$this->forwardTripleStoreError($errors);
			return;
		}
		
		$rels = array();
		foreach($rows as $row) {
			if(isset($rels[$row['to']])) 
				$rels[$row['to']] .= ',';
			else
				$rels[$row['to']] = '';

			$rels[$row['to']] .= array_search($row['p'], $this->_relationships);
		}

		$xml = '<result>';

		foreach($rels as $to => $rel) {
			$profile = $this->getProfile($to, $store);
			if($profile === false)
				return;
				
			$xml .= '<relationship from="' . $id . '"';
			
			if(isset($row['label']) && !empty($row['label'])) {
				$xml .= ' label="' . htmlentities($row['label'], ENT_COMPAT, 'UTF-8') . '"';
			}

			$xml .= ' type="' . $rel . '">';
			$xml .= $profile;
			$xml .= '</relationship>';
		}

		$xml .= '</result>';
			
		$this->outputXml($xml);
	}
}
