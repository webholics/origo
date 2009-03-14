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
				$xml .= '<profile about="' . $id . '">';

				$store = $this->getBrowserStore();
				
				foreach($this->_properties as $key => $value) {
					$query = $this->_queryPrefix .
						'SELECT ?val WHERE {' .
							'<' . $id . '> <' . $value[0] . '> ?val .' .
						'}';
					$row = $store->query($query, 'row');

					if(!$store->getErrors() && $row) {
						// some properties need special attention
						if($key == 'mbox')
							$row['val'] = substr($row['val'], 7);
					
						$xml .= '<property name="' . $key . '">' . $row['val'] . '</property>';
					}
				}
				
				$xml .= '</profile>';
			}
		}
		
		$xml .= '</result>';

		$this->outputXml($xml);
	}

	/**
	 * Load URI.
	 * Can load a profile URI and a foaf:Person if URI is dereferencable.
	 * The methods checks if profile is already in triple store.
	 *
	 * @param string $uri The uri to load.
	 * @return string|false Uri pointing to foaf:Person, or false if not found.
	 */
	protected function loadUri($uri) 
	{
		$loaded = false;
		$store = $this->getBrowserStore();

		// check if profile is already in triple store
		$ask = $this->_queryPrefix .
			'ASK WHERE {' .
				'{ <' . $uri . '> rdf:type foaf:PersonalProfileDocument }' .
				' UNION ' .
				'{ <' . $uri . '> rdf:type foaf:Person }' .
			'}';
		if(!$store->query($ask, 'raw')) {
			$query = 'LOAD <' . $uri . '> INTO <' . $uri . '>';
			$result = $store->query($query);

			if($store->getErrors() || $result['result']['t_count'] == 0)
				return false;

			$loaded = true;
		}

		// check if $uri is foaf:Person
		// otherwise try to find the correct uri
		$ask = $this->_queryPrefix .
			'ASK WHERE {' .
				'<' . $uri . '> rdf:type foaf:Person' .
			'}';
		if(!$store->query($ask, 'raw')) {
			$query = $this->_queryPrefix .
				'SELECT ?person WHERE {' .
					'<' . $uri . '> foaf:primaryTopic ?person .' .
				'}';
			$row = $store->query($query, 'row');
			
			if($store->getErrors() || !$row)
				return false;

			$identifier = $row['person'];
		}
		else
			$identifier = $uri;

		// if loaded we have to do some stuff
		if($loaded) {
			// load seeAlso properties
			$query = $this->_queryPrefix .
				'SELECT ?seealso WHERE {' . 
					'<' . $identifier . '> rdfs:seeAlso ?seealso .' .
				'} LIMIT ' . $this->_maxSeeAlso;
			$rows = $store->query($query, 'rows');
			
			// we ignore errors here
			// this is not as important to kill the whole process
			if(!$store->getErrors()) {
				foreach($rows as $row) {
					$query = 'LOAD <' . $row['seealso'] . '> INTO <' . $uri . '>';
					$store->query($query);
				}
			}

			// call inferencing script
			$this->inference($uri);
		}

		return $identifier;
	}

	/**
	 * Do some lightweight inferencing with sparql queries.
	 *
	 * @param string $graph The graph to do inferencing on.
	 * @return void
	 */
	protected function inference($graph)
	{
		// the max depth to inference
		$depthLimit = 10;

		$store = $this->getBrowserStore();
		$config = $this->getConfig();

		// subProperty inferencing
		for($d = 0; $d < $depthLimit; $d++) {
			$query = $this->_queryPrefix .
				'INSERT INTO <' . $graph . '> CONSTRUCT {' .
					'?s ?top ?o .' .
				'} WHERE {' .
					'GRAPH <' . $graph . '> {' .
						'?s ?prop ?o .' .
					'}' .
					'?prop rdfs:subPropertyOf ?top .' .
				'}';
			$result = $store->query($query);
			
			if($errors = $store->getErrors()) {
				if($config->misc->environment == 'development') {
					$die = '';
					for($i = 0; $i < count($errors); $i++) {
						$die .= $errors[$i];
						if($i < count($errors)-1)
							$die .= ' ';
					}
					die($die);
				}
				break;
			}
			else if($result['result']['t_count'] == 0)
				break;
		}
	}
}
