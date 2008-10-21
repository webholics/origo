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

// unfortunately the openid library does not yet support E_ALL nor E_STRICT
//error_reporting(E_ALL|E_STRICT);
error_reporting(E_ALL);

define('CONFIG_FILE', 'config/config.ini');

// check if configuration file exists
if(!is_file(CONFIG_FILE)) {
	die('Origo Client error: Configuration file does not exist.');
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

// check for Authentication
if($config['client']['auth'] == 'basic') {
	if(!isset($_SERVER['PHP_AUTH_USER']) || 
		$_SERVER['PHP_AUTH_USER'] != $config['client']['auth_basic_username'] ||
		$_SERVER['PHP_AUTH_PW'] != $config['client']['auth_basic_password']) {
		header('HTTP/1.0 401 Unauthorized');
		header('WWW-Authenticate: Basic realm="Origo Client"');
		die('Origo Client error: 401 Unauthorized.');
	}
}
else if($config['client']['auth'] == 'openid') {
	session_start();
	
	if(!is_writeable('tmp')) {
		die('Origo Client error: tmp directory is not writeable.');
	}
	if(!is_dir('tmp/openid')) {
		mkdir('tmp/openid');
		chmod('tmp/openid', 0775);
	}

	ini_set('include_path', 'libs/php-openid' . PATH_SEPARATOR . ini_get('include_path'));
	require_once 'Auth/OpenID/Consumer.php';
	require_once 'Auth/OpenID/FileStore.php';

	$store = new Auth_OpenID_FileStore('tmp/openid');
	$consumer = new Auth_OpenID_Consumer($store);
	
	$scheme = 'http';
	if(isset($_SERVER['https']) && $_SERVER['https'] == 'on') {
		$scheme .= 's';
	}
	$realm = $scheme . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . dirname($_SERVER['PHP_SELF']) . '/';
	$return_to = $scheme . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['PHP_SELF'];	

	$response = $consumer->complete($return_to);
	if($response->status !== Auth_OpenID_SUCCESS) {	
		$auth_request = $consumer->begin($config['client']['auth_openid']);
	
		if(!$auth_request) {
			die('Origo Client error: Not a valid OpenID.');
		}
	
		// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
		// form to send a POST request to the server.
		if($auth_request->shouldSendRedirect()) {
			$redirect_url = $auth_request->redirectURL($realm, $return_to);
			
			if(Auth_OpenID::isFailure($redirect_url)) {
				die('Origo Client error: Could not redirect to OpenID server.');
			} 
			else {
				header('Location: ' . $redirect_url);
			}
		}
		else {
			// Generate form markup and render it.
			$form_id = 'openid_message';
			$form_html = $auth_request->htmlMarkup($realm, $return_to, false, array('id' => $form_id));
	
			if(Auth_OpenID::isFailure($form_html)) {
					die('Origo Client error: Could not redirect to OpenID server.');
			} 
			else {
				die($form_html);
			}
		}
	}
}

// generate flash vars
$flashVars = 'identifier=' . urlencode($config['client']['resource'])
           . '&amp;endpoint=' . urlencode($config['endpoint']['location'])
           . '&amp;key=' . urlencode($config['endpoint']['key']);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Origo</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css" media="screen">
			html, body, #content	{ height:100%; }
			body					{ margin:0; padding:0; overflow:hidden; }
		</style>
		<script type="text/javascript" src="js/swfobject.js"></script>
		<script type="text/javascript">
			swfobject.registerObject("client", "9.0.124", "swf/expressInstall.swf");
		</script>
	</head>
	<body>
		<div id="content">
			<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%" id="client" name="client">
				<param name="movie" value="swf/client.swf" />
				<param name="menu" value="false" />
				<param name="quality" value="high" />
				<param name="allowscriptaccess" value="sameDomain" />
				<param name="flashvars" value="<?= $flashVars ?>" />
				<!--[if !IE]>-->
				<object type="application/x-shockwave-flash" data="swf/client.swf" width="100%" height="100%">
					<param name="menu" value="false" />
					<param name="quality" value="high" />
					<param name="allowscriptaccess" value="sameDomain" />
					<param name="flashvars" value="<?= $flashVars ?>" />
				<!--<![endif]-->
					<a href="http://www.adobe.com/go/getflashplayer">
						<img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" />
					</a>
				<!--[if !IE]>-->
				</object>
				<!--<![endif]-->
			</object>
		</div>
	</body>
</html>

