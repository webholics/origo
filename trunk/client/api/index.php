<?php	
/**
 * Origo - social client
 * SPARQL endpoint
 *
 * Copyright (C) 2008 Mario Volke
 * All rights reserved.
 */

// unfortunately ARC2 does not yet support E_STRICT
//error_reporting(E_ALL|E_STRICT);
error_reporting(E_ALL);

require_once '../../libs/arc2/ARC2.php';

define('CONFIG_FILE', '../../config/config.ini');

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

$endpoint_config = array(
	// mysql database access
	'db_host' => $config['database']['host'],
	'db_name' => $config['database']['name'],
	'db_user' => $config['database']['username'],
	'db_pwd' => $config['database']['password'],

	// store_name is used as table prefix
	'store_name' => 'origo',

	'endpoint_features' => array(
		'select', 'construct', 'ask', 'describe', 
		'load', 'insert', 'delete',
		'dumb'
	),

	'endpoint_timeout' => 60, /* this feature is not yet implemented in ARC2 */
	'endpoint_max_limit' => 5000,
	'endpoint_read_key' => $config['api']['key'],
	'endpoint_write_key' => $config['api']['key']
);

$ep = ARC2::getStoreEndpoint($endpoint_config);

if(!$ep->isSetUp()) {
	$ep->setUp();
}

if($errors = $ep->getErrors()) {
	$out = '';
	foreach($errors as $err) {
		$out .= $err . "\n";
	}
	die($out);
}

$ep->go();

