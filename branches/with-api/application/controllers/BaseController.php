<?php
/**
* Origo - social client
* BaseController
*
* @copyright Copyright (c) 2008-2009 Mario Volke
* @author    Mario Volke <mario.volke@webholics.de>
*/

require_once 'library/arc2/ARC2.php';

class BaseController extends Zend_Controller_Action
{
	/**
	 * Singleton variables
	 */
	protected static $_config = null;
	protected static $_cache = null;
	protected static $_profileStore = null;
	protected static $_browserStore = null;

	/**
	 * This prefix will be prepended to all sparql queries.
	 */
	protected $_queryPrefix = '
		PREFIX foaf: <http://xmlns.com/foaf/0.1/>
		PREFIX rel: <http://purl.org/vocab/relationship/>
	';
	
	/**
	 * Get global config.
	 * Implements singleton.
	 * @return Zend_Config
	 */
	protected function getConfig()
	{
		if(is_null(self::$_config)) 
			self::$_config = Zend_Registry::get('config');
		return self::$_config;
	}

	/**
	 * Get cache. Will be initialized the first time.
	 * Implements singleton.
	 * @return Zend_Cache_Core
	 */
	protected function getCache()
	{
		if(is_null(self::$_cache)) {
			$config = $this->getConfig();

			// setup cache
			$frontendOptions = array(
			   'caching' => $config->caching->active,
			   'lifetime' => $config->caching->lifetime
			);
			$backendOptions = array(
				'cache_dir' => APPLICATION_PATH . '/../' . $config->caching->dir
			);
			// getting a Zend_Cache_Core object
			self::$_cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
		}

		return self::$_cache;
	}

	/**
	 * Construct the personal URI.
	 * @return string
	 */
	protected function getIdentifier() {
		$config = $this->getConfig();
		
		// check if identifier is a hash
		if($config->profile->identifier[0] == '#')
			return $config->profile->location . $config->profile->identifier;

		return $config->profile->identifier;
	}
	
	/**
	 * Setup profile in the triple store.
	 * Create a connection to the profile triple store
	 * and do setup and initialisiation if needed.
	 * @return ARC2_Store
	 */
	protected function getProfileStore() {
		if(is_null(self::$_profileStore)) {
			$config = $this->getConfig();
			
			$store_config = array(
				// mysql database access
				'db_host' => $config->database->params->host,
				'db_name' => $config->database->params->dbname,
				'db_user' => $config->database->params->username,
				'db_pwd' => $config->database->params->password,

				// stone_name is used as table prefix
				'store_name' => $config->arc->store->profile->name,
			);

			$store = ARC2::getStore($store_config);

			if(!$store->isSetUp()) {
				$store->setUp();
			}

			$uri = $config->profile->location;
			$identifier = $this->getIdentifier();

			// setup personal profile document
			$ask = $this->_queryPrefix .
				'ASK WHERE {' .
					'<' . $uri . '> rdf:type foaf:PersonalProfileDocument' .
				'}';
			if(!$store->query($ask, 'raw')) {
				$query = $this->_queryPrefix .
					'INSERT INTO <' . $uri . '> {' .
						'<' . $uri . '> rdf:type foaf:PersonalProfileDocument .' .
						'<' . $uri . '> foaf:maker <' . $identifier . '> .' .
						'<' . $uri . '> foaf:primaryTopic <' . $identifier . '> .' .
					'}';
				$store->query($query);
			}
			else {
				$ask = $this->_queryPrefix .
					'ASK WHERE {' . 
						'<' . $uri . '> foaf:maker <' . $identifier . '>' .
					'}';
				if(!$store->query($ask, 'raw')) {
					// delete old triples
					$query = $this->_queryPrefix . 
						'DELETE {' . 
							'<' . $uri . '> foaf:maker ?any .' .
						'}';
					$store->query($query);
					
					// insert new triple
					$query = $this->_queryPrefix . 
						'INSERT INTO <' . $uri . '> {' . 
							'<' . $uri . '> foaf:maker <' . $identifier . '> .' .
						'}';
					$store->query($query);
				}
				
				$ask = $this->_queryPrefix .
					'ASK WHERE {' . 
						'<' . $uri . '> foaf:primaryTopic <' . $identifier . '>' .
					'}';
				if(!$store->query($ask, 'raw')) {
					// delete old triples
					$query = $this->_queryPrefix . 
						'DELETE {' . 
							'<' . $uri . '> foaf:primaryTopic ?any .' .
						'}';
					$store->query($query);
					
					// insert new triple
					$query = $this->_queryPrefix . 
						'INSERT INTO <' . $uri . '> {' . 
							'<' . $uri . '> foaf:primaryTopic <' . $identifier . '> .' .
						'}';
					$store->query($query);
				}
			}

			// setup person
			$ask = $this->_queryPrefix .
				'ASK WHERE {' .
					'<' . $identifier . '> rdf:type foaf:Person' .
				'}';
			if(!$store->query($ask, 'raw')) {
				$query = $this->_queryPrefix .
					'INSERT INTO <' . $uri . '> {' .
						'<' . $identifier . '> rdf:type foaf:Person .' .
					'}';
				$store->query($query);
			}

			self::$_profileStore = $store;
		}

		return self::$_profileStore;
	}
	
	/**
	 * Create a connection to the browser triple store
	 * and do initialisiation if needed.
	 * @return ARC2_Store
	 */
	protected function getBrowserStore() {
		if(is_null(self::$_browserStore)) {
			$config = $this->getConfig();
			
			$store_config = array(
				// mysql database access
				'db_host' => $config->database->params->host,
				'db_name' => $config->database->params->dbname,
				'db_user' => $config->database->params->username,
				'db_pwd' => $config->database->params->password,

				// stone_name is used as table prefix
				'store_name' => $config->arc->store->browser->name,
			);

			$store = ARC2::getStore($store_config);

			if(!$store->isSetUp()) {
				$store->setUp();
			}
			
			// prepare browser graph
			// load diverse ontologies
			$ask = 
				'ASK WHERE {' .
				'?s ?p ?o' .
				'}';
			if(!$store->query($ask, 'raw')) {
				$default_vocabs = array(
					'http://xmlns.com/foaf/spec/index.rdf',
					'http://purl.org/vocab/relationship/rel-vocab-20040308.rdf',
				);
				foreach($default_vocabs as $vocab) {
					$store->query('LOAD <' . $vocab . '>');
				}

				// ignore errors
				if($store->getErrors())
					$store->errors = array();
			}

			self::$_browserStore = $store;
		}

		return self::$_browserStore;
	}
}
