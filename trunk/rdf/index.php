<?php	
/**
 * Origo 
 * Copyright (C) 2008 Mario Volke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// unfortunately ARC2 does not yet support E_STRICT
//error_reporting(E_ALL|E_STRICT);
error_reporting(E_ALL);

require_once 'libs/arc/ARC2.php';

define('CONFIG_FILE', 'config/config.ini');

// check if configuration file exists
if(!is_file(CONFIG_FILE)) {
	die('Origo RDF Distributor error: Configuration file does not exist.');
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

if(!is_dir($config['cache']['dir'])) {
	die('Origo RDF Distributor error: Cache directory does not exist.');
}

if(!is_readable($config['cache']['dir'])) {
	die('Origo RDF Distributor error: Cache directory is not readable.');
}

if(!is_writable($config['cache']['dir'])) {
	die('Origo RDF Distributor error: Cache directory is not writable.');
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

