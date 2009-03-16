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

	public function preDispatch()
	{
		return parent::preDispatch();
	}

	/**
	 * Clean action.
	 * Deletes all data stored in browser triple store.
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
		}

		$xml .= '</result>';

		$this->outputXml($xml);
	}

	/**
	 * Profile action
	 * Get a profile.
	 * Only GET params.
	 * Needs param: uri
	 */
	public function profileAction()
	{
		$xml = '<result>';

		$params = $this->getRequest()->getQuery();
		if(!isset($params['uri']) || empty($params['uri'])) {
			$xml .= '<error>Not all parameters given. This method needs: uri</error>';
		}
		else {
			$uri = $this->escape($params['uri'], 'uri');
			$id = $this->loadUri($uri);

			if($id === false) {
				$xml .= '<error>Could not find/load profile.</error>';
			}
			else {
				$xml .= $this->getProfile($id, $store);
			}
		}
		
		$xml .= '</result>';

		$this->outputXml($xml);
	}
	
	/**
	 * Relationships action
	 * Get relationships limited to the ones provided by GET params
	 * or all relationships if no params provided.
	 * Only GET params.
	 * Needs param: uri
	 */
	public function relationshipsAction()
	{
		$xml = '<result>';

		$params = $this->getRequest()->getQuery();
		if(!isset($params['uri']) || empty($params['uri'])) {
			$xml .= '<error>Not all parameters given. This method needs: uri</error>';
		}
		else {
			$uri = $this->escape($params['uri'], 'uri');
			$id = $this->loadUri($uri);

			if($id === false) {
				$xml .= '<error>Could not find/load profile.</error>';
			}
			else {
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
						if(isset($rels[$row['to']])) 
							$rels[$row['to']] .= ',';
						else
							$rels[$row['to']] = '';

						$rels[$row['to']] .= array_search($row['p'], $this->_relationships);
					}

					foreach($rels as $to => $rel) {
						$xml .= '<relationship';
						
						if(isset($row['label']) && !empty($row['label'])) {
							$xml .= ' label="' . htmlentities($row['label'], ENT_COMPAT, 'UTF-8') . '"';
						}

						$xml .= ' type="' . $rel . '">';
						$xml .= $this->getProfile($to, $store);
						$xml .= '</relationship>';
					}
				}
			}
		}

		$xml .= '</result>';
			
		$this->outputXml($xml);
	}
}
