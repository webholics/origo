<?php
/**
* Origo - social client
* BaseController
*
* @copyright (C) 2008-2009 Mario Volke, All right reserved.
* @author Mario Volke (mario.volke@webholics.de)
*/

require_once 'library/arc2/ARC2.php';

class BaseController extends Zend_Controller_Action
{
	protected static $_config = null;
	protected static $_cache = null;
	
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

		$prefix = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> . ';

		$uri = $config->profile->location;
		$identifier = $this->getIdentifier();

		// setup personal profile document
		$ask = $prefix .
			'ASK WHERE {' .
				'<' . $uri . '> rdf:type foaf:PersonalProfileDocument' .
			'}';
		if(!$store->query($ask, 'raw')) {
			$query = $prefix .
				'INSERT INTO <' . $uri . '> {' .
					'<' . $uri . '> rdf:type foaf:PersonalProfileDocument .' .
					'<' . $uri . '> foaf:maker <' . $identifier . '> .' .
					'<' . $uri . '> foaf:primaryTopic <' . $identifier . '> .' .
				'}';
			$store->query($query);
		}
		else {
			$ask = $prefix .
				'ASK WHERE {' . 
					'<' . $uri . '> foaf:maker <' . $identifier . '>' .
				'}';
			if(!$store->query($ask, 'raw')) {
				// delete old triples
				$query = $prefix . 
					'DELETE {' . 
						'<' . $uri . '> foaf:maker ?any .' .
					'}';
				$store->query($query);
				
				// insert new triple
				$query = $prefix . 
					'INSERT INTO <' . $uri . '> {' . 
						'<' . $uri . '> foaf:maker <' . $identifier . '> .' .
					'}';
				$store->query($query);
			}
			
			$ask = $prefix .
				'ASK WHERE {' . 
					'<' . $uri . '> foaf:primaryTopic <' . $identifier . '>' .
				'}';
			if(!$store->query($ask, 'raw')) {
				// delete old triples
				$query = $prefix . 
					'DELETE {' . 
						'<' . $uri . '> foaf:primaryTopic ?any .' .
					'}';
				$store->query($query);
				
				// insert new triple
				$query = $prefix . 
					'INSERT INTO <' . $uri . '> {' . 
						'<' . $uri . '> foaf:primaryTopic <' . $identifier . '> .' .
					'}';
				$store->query($query);
			}
		}

		// setup person
		$ask = $prefix .
			'ASK WHERE {' .
				'<' . $identifier . '> rdf:type foaf:Person' .
			'}';
		if(!$store->query($ask, 'raw')) {
			$query = $prefix .
				'INSERT INTO <' . $uri . '> {' .
					'<' . $identifier . '> rdf:type foaf:Person .' .
				'}';
			$store->query($query);
		}

		return $store;
	}
}
