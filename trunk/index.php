<?php	
/**
 * Origo - social client
 * content negotiatior
 *
 * Copyright (C) 2008 Mario Volke
 * All rights reserved.
 */

require_once 'libs/php-content-negotiation/content_negotiation.inc.php';

require 'includes/startup.php';
require 'includes/currentUri.php';
 
$uri = currentUri();

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

