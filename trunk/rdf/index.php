<?php	
/**
 * Origo - social client
 * RDF distributor
 *
 * Copyright (C) 2008 Mario Volke
 * All right reserved.
 */

require_once '../libs/arc2/ARC2.php';

require '../includes/startup.php';
require_once '../includes/setupProfile.php';

$file = TMP_DIR . '/' . TMP_RDF_FILE;
// check if cache file is too old
if(!is_file($file) || time() > filemtime($file) + $config['cache']['lifetime']) {
	$store_config = array(
		// mysql database access
		'db_host' => $config['database']['host'],
		'db_name' => $config['database']['name'],
		'db_user' => $config['database']['username'],
		'db_pwd' => $config['database']['password'],

		// stone_name is used as table prefix
		'store_name' => 'origo'
	);

	$store = ARC2::getStore($store_config);

	setupProfile($store, $config);
	
	$query = "CONSTRUCT {"
	       . "  ?s ?p ?o ."
		   . "}"
		   . "WHERE {"
		   . "  GRAPH <" . IDENTITY_GRAPH . "> {"
		   . "	  ?s ?p ?o ."
		   . "  }"
		   . "}";

	$index = $store->query($query, 'raw');

	// we want nice namespaces
	$ns = array(
		'foaf' => 'http://xmlns.com/foaf/0.1/',
		'dc' => 'http://purl.org/dc/elements/1.1/',
		'owl' => 'http://www.w3.org/2002/07/owl#',
		'vcard' => 'http://www.w3.org/2001/vcard-rdf/3.0#',
		'skos' => 'http://www.w3.org/2004/02/skos/core#',
		'sioc' => 'http://rdfs.org/sioc/ns#',
		'xfn' => 'http://gmpg.org/xfn/11#'
	);

	$serializer_config = array(
		'ns' => $ns,
		'serializer_prettyprint_containers' => true
	);

	$ser = ARC2::getRDFXMLSerializer($serializer_config);
	$doc = $ser->getSerializedIndex($index);
	
	if($errors = $store->getErrors()) {
		$out = '';
		foreach($errors as $err) {
			$out .= $err . "\n";
		}
		die($out);
	}

	if(file_put_contents($file, $doc) === false) {
		die('Origo RDF Distributor error: Could not write cache file.');
	}
}

header('Content-Type: application/rdf+xml;charset=utf-8');
echo file_get_contents($file);

