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

define('CONFIG_FILE', 'config/config.ini');

// check if configuration file exists
if(!is_file(CONFIG_FILE)) {
	die('Origo SPARQL Endpoint Proxy error: Configuration file does not exist.');
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

$curl = curl_init();

$url = $config['endpoint']['location'];

// set the correct request method
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($_POST));
}
else {
	curl_setopt($curl, CURLOPT_HTTPGET, true);
	if(!empty($_GET)) {
		$url .= (stripos($url, '?') !== false) ? '&' : '?';
		$url .= http_build_query($_GET);
	}
}

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// construct headers
$headers = array();
if(isset($_SERVER['HTTP_USER_AGENT'])) {
	$headers[] = 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'];
}
if(isset($_SERVER['HTTP_REFERER'])) {
	$headers[] = 'Referer: ' . $_SERVER['HTTP_REFERER'];
}
if(isset($_SERVER['HTTP_ACCEPT'])) {
	$headers[] = 'Accept: ' . $_SERVER['HTTP_ACCEPT'];
}
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	$headers[] = 'Accept-Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'];
}
if(isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
	$headers[] = 'Accept-Encoding: ' . $_SERVER['HTTP_ACCEPT_ENCODING'];
}
if(isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
	$headers[] = 'Accept-Charset: ' . $_SERVER['HTTP_ACCEPT_CHARSET'];
}
if(isset($_SERVER['HTTP_CONNECTION'])) {
	$headers[] = 'Connection: ' . $_SERVER['HTTP_CONNECTION'];
}
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

// grab URL
$response = curl_exec($curl);

if($response === false) {
	die('Origo SPARQL Endpoint Proxy error: Could not establish a connection to the SPARQL endpoint.');
}

// pass the response to the browser
header('Content-Type: ' . curl_getinfo($curl, CURLINFO_CONTENT_TYPE));
echo $response;

curl_close($curl);
