<?php	
/**
 * Origo - social client
 * content negotiatior
 *
 * Copyright (C) 2008 Mario Volke
 * All rights reserved.
 */
 
error_reporting(E_ALL|E_STRICT);

require_once 'libs/php-content-negotiation/content_negotiation.inc.php';

define('CONFIG_FILE', 'config/config.ini');

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

// construct the complete request URI
$protocol = strtolower($_SERVER['SERVER_PROTOCOL']);
$protocol = substr($protocol, 0, strpos($protocol, '/'));
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	$protocol .= 's';
}

$port = '';
if($_SERVER['SERVER_PORT'] != 80) {
	$port = ':' . $_SERVER['SERVER_PORT'];
}

$uri = $protocol . '://' . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI']; 
// remove trailing slash
$uri = rtrim($uri, '/');

// content negotiation
$mime_types = array(
	'type' => array(
		'application/rdf+xml'	
	),
	'app_preference' => array(
		1.0	
	)
);
$uris = array(
	'application/rdf+xml' => $uri . '/rdf'	
);

// add users website
if(isset($config['negotiation']['homepage']) && !empty($config['negotiation']['homepage'])) {
	$mime_types['type'][] = 'text/html';
	$mime_types['app_preference'][] = 0.8;
	$uris['text/html'] = $config['negotiation']['homepage'];
}

$mime_best = content_negotiation::mime_best_negotiation($mime_types);

header('HTTP/1.1 303 See Other');
header('Location: ' . $uris[$mime_best]);
