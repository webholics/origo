<?php
/**
* Origo - social client
* ProfileController
*
* @copyright (C) 2008-2009 Mario Volke, All right reserved.
* @author Mario Volke (mario.volke@webholics.de)
*/

require_once 'library/php-content-negotiation/content_negotiation.inc.php';

class ProfileController extends BaseController
{
	public function init()
	{
		parent::init();
		$this->_helper->viewRenderer->setNoRender();
	}

	/**
	 * Index action is the main entry point for a user's profile
	 * We use content negotiation in order to redirect to 
	 * a content type appropriate for the request.
	 */
	public function indexAction() 
	{
		// fetch absolute request uri
		$this->getRequest()->getRequestUri();

		// content negotiation
		$mime_types = array(
			'type' => array(
				'application/rdf+xml',
				'application/turtle',
				'application/json',
				'text/html'
			),
			'app_preference' => array(
				1.0,
				0.6,
				0.6,
				0.8
			)
		);
		$uris = array(
			'application/rdf+xml' => $this->view->url(array(), 'profile_rdf'),
			'application/turtle' => $this->view->url(array(), 'profile_turtle'),	
			'application/json' => $this->view->url(array(), 'profile_json')	
		);

		// add html ressource
		$config = $this->getConfig();
		if(!empty($config->negotiation->html))
			$uris['text/html'] = $config->negotiation->html;
		else
			$uris['text/html'] = $this->view->url(array(), 'profile_html');

		$mime_best = content_negotiation::mime_best_negotiation($mime_types);

		// redirect with 303 See Other
		$this->_helper->Redirector
			->setCode(303)
			->setPrependBase()
			->gotoUrl($uris[$mime_best]);
	}

	/**
	 * Publish RDF/XML profile.
	 * Profile will be cached as configured.
	 */
	public function rdfAction()
	{
		$cache = $this->getCache();
		$cacheId = 'profile_rdf';
		if(!$doc = $cache->load($cacheId)) {
			$config = $this->getConfig();
			$store = $this->getProfileStore();

			$query = '
				CONSTRUCT { 
					?s ?p ?o .
				}
				WHERE {
					?s ?p ?o .
				}
			';

			$index = $store->query($query, 'raw');

			// we want nice namespaces
			$ns = array(
				'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
				'foaf' => 'http://xmlns.com/foaf/0.1/',
				'dc' => 'http://purl.org/dc/elements/1.1/',
				'dct' => 'http://purl.org/dc/terms/',
				'owl' => 'http://www.w3.org/2002/07/owl#',
				'vcard' => 'http://www.w3.org/2001/vcard-rdf/3.0#',
				'skos' => 'http://www.w3.org/2004/02/skos/core#',
				'sioc' => 'http://rdfs.org/sioc/ns#',
				'xfn' => 'http://gmpg.org/xfn/11#',
				'rel' => 'http://purl.org/vocab/relationship/'
			);

			$serializer_config = array(
				'ns' => $ns,
				'serializer_prettyprint_containers' => true
			);

			$ser = ARC2::getRDFXMLSerializer($serializer_config);
			$doc = $ser->getSerializedIndex($index);
				
			if($errors = $store->getErrors()) {
				if($config->misc->environment == 'development') {
					$out = '';
					foreach($errors as $err) {
						$out .= $err . "\n";
					}
					echo $out;
				}
				die();
			}

			$cache->save($doc, $cacheId, array('profile'));
		}

		header('Content-Type: application/rdf+xml;charset=utf-8');
		echo $doc;
	}

	/**
	 * Publish Turtle profile.
	 * Profile will be cached as configured.
	 */
	public function turtleAction()
	{
		$cache = $this->getCache();
		$cacheId = 'profile_turtle';
		if(!$doc = $cache->load($cacheId)) {
			$config = $this->getConfig();
			$store = $this->getProfileStore();

			$query = '
				CONSTRUCT { 
					?s ?p ?o .
				}
				WHERE {
					?s ?p ?o .
				}
			';

			$index = $store->query($query, 'raw');

			// we want nice namespaces
			$ns = array(
				'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
				'foaf' => 'http://xmlns.com/foaf/0.1/',
				'dc' => 'http://purl.org/dc/elements/1.1/',
				'dct' => 'http://purl.org/dc/terms/',
				'owl' => 'http://www.w3.org/2002/07/owl#',
				'vcard' => 'http://www.w3.org/2001/vcard-rdf/3.0#',
				'skos' => 'http://www.w3.org/2004/02/skos/core#',
				'sioc' => 'http://rdfs.org/sioc/ns#',
				'xfn' => 'http://gmpg.org/xfn/11#',
				'rel' => 'http://purl.org/vocab/relationship/'
			);

			$serializer_config = array(
				'ns' => $ns
			);

			$ser = ARC2::getTurtleSerializer($serializer_config);
			$doc = $ser->getSerializedIndex($index);
				
			if($errors = $store->getErrors()) {
				if($config->misc->environment == 'development') {
					$out = '';
					foreach($errors as $err) {
						$out .= $err . "\n";
					}
					echo $out;
				}
				die();
			}

			$cache->save($doc, $cacheId, array('profile'));
		}

		header('Content-Type: application/turtle;charset=utf-8');
		echo $doc;
	}

	/**
	 * Publish RDF/JSON profile.
	 * Profile will be cached as configured.
	 */
	public function jsonAction()
	{
		$cache = $this->getCache();
		$cacheId = 'profile_json';
		if(!$doc = $cache->load($cacheId)) {
			$config = $this->getConfig();
			$store = $this->getProfileStore();

			$query = '
				CONSTRUCT { 
					?s ?p ?o .
				}
				WHERE {
					?s ?p ?o .
				}
			';

			$index = $store->query($query, 'raw');

			// we want nice namespaces
			$ns = array(
				'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
				'foaf' => 'http://xmlns.com/foaf/0.1/',
				'dc' => 'http://purl.org/dc/elements/1.1/',
				'dct' => 'http://purl.org/dc/terms/',
				'owl' => 'http://www.w3.org/2002/07/owl#',
				'vcard' => 'http://www.w3.org/2001/vcard-rdf/3.0#',
				'skos' => 'http://www.w3.org/2004/02/skos/core#',
				'sioc' => 'http://rdfs.org/sioc/ns#',
				'xfn' => 'http://gmpg.org/xfn/11#',
				'rel' => 'http://purl.org/vocab/relationship/'
			);

			$serializer_config = array(
				'ns' => $ns
			);

			$ser = ARC2::getRDFJSONSerializer($serializer_config);
			$doc = $ser->getSerializedIndex($index);
				
			if($errors = $store->getErrors()) {
				if($config->misc->environment == 'development') {
					$out = '';
					foreach($errors as $err) {
						$out .= $err . "\n";
					}
					echo $out;
				}
				die();
			}

			$cache->save($doc, $cacheId, array('profile'));
		}

		header('Content-Type: application/json;charset=utf-8');
		echo $doc;
	}

	/**
	 * Publish POSH RDF profile.
	 * Profile will be cached as configured.
	 */
	public function htmlAction()
	{
		$cache = $this->getCache();
		$cacheId = 'profile_html';
		if(!$doc = $cache->load($cacheId)) {
			$config = $this->getConfig();
			$store = $this->getProfileStore();

			$query = '
				CONSTRUCT { 
					?s ?p ?o .
				}
				WHERE {
					?s ?p ?o .
				}
			';

			$index = $store->query($query, 'raw');

			// we want nice namespaces
			$ns = array(
				'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
				'foaf' => 'http://xmlns.com/foaf/0.1/',
				'dc' => 'http://purl.org/dc/elements/1.1/',
				'dct' => 'http://purl.org/dc/terms/',
				'owl' => 'http://www.w3.org/2002/07/owl#',
				'vcard' => 'http://www.w3.org/2001/vcard-rdf/3.0#',
				'skos' => 'http://www.w3.org/2004/02/skos/core#',
				'sioc' => 'http://rdfs.org/sioc/ns#',
				'xfn' => 'http://gmpg.org/xfn/11#',
				'rel' => 'http://purl.org/vocab/relationship/'
			);

			$serializer_config = array(
				'ns' => $ns
			);

			$ser = ARC2::getPOSHRDFSerializer($serializer_config);
			$doc = $ser->getSerializedIndex($index);
				
			if($errors = $store->getErrors()) {
				if($config->misc->environment == 'development') {
					$out = '';
					foreach($errors as $err) {
						$out .= $err . "\n";
					}
					echo $out;
				}
				die();
			}

			$cache->save($doc, $cacheId, array('profile'));
		}

		$this->view->doc = $doc;
		$this->view->identifier = $this->getIdentifier();
		$this->render();
	}
}
