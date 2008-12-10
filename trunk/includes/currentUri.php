<?php
/**
* Origo - social client
*
* Copyright (C) 2008 Mario Volke
* All right reserved.
*/

/** 
 * Reconstruct the current requested URI from the $_SERVER variables.
 * @return string the current requested URI
 */
function currentUri() {
	strtolower($_SERVER['SERVER_PROTOCOL']);
	$protocol = substr($protocol, 0, strpos($protocol, '/'));
	
	$port = '';
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	    $protocol .= 's';
		if($_SERVER['SERVER_PORT'] != 443) {
		    $port = ':' . $_SERVER['SERVER_PORT'];
		}
	}
	else if($_SERVER['SERVER_PORT'] != 80) {
	    $port = ':' . $_SERVER['SERVER_PORT'];
	}
	
	$uri = $protocol . '://' . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI']; 
	
	// remove trailing slash
	$uri = rtrim($uri, '/');

	return $uri;
}

