<?php
/**
* Origo - social client
*
* Copyright (C) 2008 Mario Volke
* All right reserved.
*/

require_once dirname(__FILE__) . '/identifier.php';

/**
 * Construct the URI for the backup graph.
 * @param $graph The graph URI to generate a backup graph URI.
 * @return The URI of the backup graph.
 */
function getBackupGraph($graph) {
	return $graph . ':backup';
}

/**
 * Setup profile in the triple store.
 * @param $store A conncetion to the triple store.
 * @param $config The config array loaded from the ini config file.
 */
function setupProfile($store, $config) {
	if(!$store->isSetUp()) {
		$store->setUp();
	}

	$prefix = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> . ';

	$uri = $config['global']['document_uri'];
	$identifier = identifier($config);
	
	// check if there is backuped data
	$query = 'SELECT ?s ?p ?o WHERE {' .
		'GRAPH <' . getBackupGraph(IDENTITY_GRAPH) . '> {' .
		'?s ?p ?o .' .
		'}' .
		'}';
	$rs = $store->query($query);
	if(sizeof($rs['result']['rows']) > 0) {
		return;
	}
	
	// setup personal profile document
	$ask = $prefix .
		'ASK WHERE {' .
		'GRAPH <' . IDENTITY_GRAPH . '> {' .
		'<' . $uri . '> rdf:type foaf:PersonalProfileDocument' .
		'} }';
	if(!$store->query($ask, 'raw')) {
		$query = $prefix .
			'INSERT INTO <' . IDENTITY_GRAPH . '> {' .
			'<' . $uri . '> rdf:type foaf:PersonalProfileDocument .' .
			'<' . $uri . '> foaf:maker <' . $identifier . '> .' .
			'<' . $uri . '> foaf:primaryTopic <' . $identifier . '> .' .
			'}';
		$store->query($query);
	}
	else {
		$ask = $prefix .
			'ASK WHERE {' . 
			'GRAPH <' . IDENTITY_GRAPH . '> {' .
			'<' . $uri . '> foaf:maker <' . $identifier . '>' .
			'} }';
		if(!$store->query($ask, 'raw')) {
			// delete old triples
			$query = $prefix . 
				'DELETE FROM <' . IDENTITY_GRAPH . '> {' . 
				'<' . $uri . '> foaf:maker ?any .' .
				'}';
			$store->query($query);
			
			// insert new triple
			$query = $prefix . 
				'INSERT INTO <' . IDENTITY_GRAPH . '> {' . 
				'<' . $uri . '> foaf:maker <' . $identifier . '> .' .
				'}';
			$store->query($query);
		}
		
		$ask = $prefix .
			'ASK WHERE {' . 
			'GRAPH <' . IDENTITY_GRAPH . '> {' .
			'<' . $uri . '> foaf:primaryTopic <' . $identifier . '>' .
			'} }';
		if(!$store->query($ask, 'raw')) {
			// delete old triples
			$query = $prefix . 
				'DELETE FROM <' . IDENTITY_GRAPH . '> {' . 
				'<' . $uri . '> foaf:primaryTopic ?any .' .
				'}';
			$store->query($query);
			
			// insert new triple
			$query = $prefix . 
				'INSERT INTO <' . IDENTITY_GRAPH . '> {' . 
				'<' . $uri . '> foaf:primaryTopic <' . $identifier . '> .' .
				'}';
			$store->query($query);
		}
	}

	// setup person
	$ask = $prefix .
		'ASK WHERE {' .
		'GRAPH <' . IDENTITY_GRAPH . '> {' .
		'<' . $identifier . '> rdf:type foaf:Person' .
		'} }';
	if(!$store->query($ask, 'raw')) {
		$query = $prefix .
			'INSERT INTO <' . IDENTITY_GRAPH . '> {' .
			'<' . $identifier . '> rdf:type foaf:Person .' .
			'}';
		$store->query($query);
	}
}

