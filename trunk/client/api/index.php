<?php	
/**
 * Origo - social client
 * SPARQL endpoint
 *
 * Copyright (C) 2008 Mario Volke
 * All rights reserved.
 */
 
require_once '../../libs/arc2/ARC2.php';

require '../../includes/startup.php';
require_once '../../includes/setupProfile.php';

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
	'endpoint_read_key' => $config['client']['api_key'],
	'endpoint_write_key' => $config['client']['api_key']
);

$ep = ARC2::getStoreEndpoint($endpoint_config);

setupProfile($ep, $config);

if($errors = $ep->getErrors()) {
	$out = '';
	foreach($errors as $err) {
		$out .= $err . "\n";
	}
	die($out);
}

$ep->go();

