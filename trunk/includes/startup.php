<?php
/**
* Origo - social client
* startup, initialize php environment and define globals
*
* Copyright (C) 2008 Mario Volke
* All right reserved.
*/

// unfortunately ARC2 does not yet support E_STRICT
//error_reporting(E_ALL|E_STRICT);
error_reporting(E_ALL);

define('CONFIG_FILE', realpath(dirname(__FILE__) . '/../config/config.ini'));
define('TMP_DIR', realpath(dirname(__FILE__) . '/../tmp'));
define('TMP_RDF_FILE', 'data.rdf');
define('IDENTITY_GRAPH', 'urn:origo:identity');

// check if configuration file exists
if(!is_file(CONFIG_FILE)) {
	die('Origo error: Configuration file does not exist.');
}

// load configuration from ini file
$config = parse_ini_file(CONFIG_FILE, true);

// should be disabled on prodution servers
if($config['global']['display_errors'] == 1) {
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
