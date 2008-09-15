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
 
error_reporting(E_ALL|E_STRICT);

require_once 'libs/php_content_negotiation/content_negotiation.inc.php';

define('CONFIG_FILE', 'config/config.ini');

// check if configuration file exists
if(!is_file(CONFIG_FILE)) {
	die('Origo Resource Identifier error: Configuration file does not exist.');
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

if($uri != $config['resource']['identifier']) {
	header('HTTP/1.0 404 Not Found');
	die('Origo Resource Identifier error: 404 Resource not found.');
}

$mime_types = array(
	'type' => array(),
	'app_preference' => array()
);
$uris = array();
foreach($config['mime_types'] as $key => $mime) {
	$pref = 1.0;
	if(isset($config['preferences'][$key])) {
		$pref = $config['preferences'][$key];
	}
	if(isset($config['uris'][$key]) && !empty($config['uris'][$key])) {
		$mime_types['type'][] = $mime;
		$mime_types['app_preference'][] = $pref;
		$uris[$mime] = $config['uris'][$key];
	}
}

$mime_best = content_negotiation::mime_best_negotiation($mime_types);

header('HTTP/1.1 303 See Other');
header('Location: ' . $uris[$mime_best]);

