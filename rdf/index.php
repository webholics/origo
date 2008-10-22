<?php	
/**
 * Origo - social client
 * RDF distributor
 *
 * Copyright (C) 2008 Mario Volke
 * All right reserved.
 */

// unfortunately ARC2 does not yet support E_STRICT
//error_reporting(E_ALL|E_STRICT);
error_reporting(E_ALL);

require_once '../libs/arc/ARC2.php';

define('CONFIG_FILE', '../config/config.ini');
define('TMP_DIR', '../tmp');

// check if configuration file exists
if(!is_file(CONFIG_FILE)) {
	die('Origo error: Configuration file does not exist.');
}

// load configuration from ini file
$config = parse_ini_file(CONFIG_FILE, true);

// should be disabled on prodution servers
if($config['misc']['display_errors'] == 1) {
	ini_set('display_errors', 1);
}
else {
	ini_set('display_errors', 0);
}

if(!is_dir(TMP_DIR)) {
	die('Origo error: Tmp directory does not exist.');
}

if(!is_readable(TMP_DIR)) {
	die('Origo error: Tmp directory is not readable.');
}

if(!is_writable(TMP_DIR)) {
	die('Origo error: Tmp directory is not writable.');
}

$file = $config['cache']['dir'] . '/' . $config['cache']['file'];
// check if cache file is too old
if(!is_file($file) || time() > filemtime($file) + $config['cache']['lifetime']) {
	$store_config = array(
		'remote_store_endpoint' => $config['endpoint']['location'] . '?key=' . urlencode($config['endpoint']['key'])
	);

	$store = ARC2::getRemoteStore($store_config);
	
	$query = "CONSTRUCT {"
	       . "  ?s ?p ?o ."
		   . "}"
		   . "WHERE {"
		   . "  GRAPH <" . $config['distributor']['resource'] . "> {"
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

	$ser = ARC2::getRDFXMLSerializer(array('ns' => $ns));
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

