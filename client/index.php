<?php	
/**
 * Origo - social client
 * client
 *
 * Copyright (C) 2008 Mario Volke
 * All rights reserved.
 */

// unfortunately the openid library does not yet support E_ALL nor E_STRICT
//error_reporting(E_ALL|E_STRICT);
error_reporting(E_ALL);

define('CONFIG_FILE', '../config/config.ini');
define('TMP_DIR', '../tmp');

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

// assemble Origo URI
$scheme = 'http';
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	$scheme .= 's';
}
$origo_uri = $scheme . '://' . $_SERVER['SERVER_NAME'];
if(
	((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') && $_SERVER['SERVER_PORT'] != 80) ||
	(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['SERVER_PORT'] != 443)
) {
	$origo_uri .= ':' . $_SERVER['SERVER_PORT'];
}
$origo_uri .= substr(dirname($_SERVER['PHP_SELF']), 0, -7);

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
	
	if(!is_writeable(TMP_DIR)) {
		die('Origo error: tmp directory is not writeable.');
	}
	if(!is_dir(TMP_DIR . '/openid')) {
		mkdir(TMP_DIR . '/openid');
		chmod(TMP_DIR . '/openid', 0775);
	}

	ini_set('include_path', 'libs/php-openid' . PATH_SEPARATOR . ini_get('include_path'));
	require_once 'Auth/OpenID/Consumer.php';
	require_once 'Auth/OpenID/FileStore.php';

	$store = new Auth_OpenID_FileStore(TMP_DIR . '/openid');
	$consumer = new Auth_OpenID_Consumer($store);
	
	$realm = $origo_uri . '/client';
	$return_to = $origo_uri . '/client';	

	$response = $consumer->complete($return_to);
	if($response->status !== Auth_OpenID_SUCCESS) {	
		$auth_request = $consumer->begin($config['client']['auth_openid']);
	
		if(!$auth_request) {
			die('Origo error: Not a valid OpenID.');
		}
	
		// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
		// form to send a POST request to the server.
		if($auth_request->shouldSendRedirect()) {
			$redirect_url = $auth_request->redirectURL($realm, $return_to);
			
			if(Auth_OpenID::isFailure($redirect_url)) {
				die('Origo error: Could not redirect to OpenID server.');
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
					die('Origo error: Could not redirect to OpenID server.');
			} 
			else {
				die($form_html);
			}
		}
	}
}

if($config['global']['identifier'][0] == '#') {
	$identifier = $origo_uri . '/' . $config['global']['identifier'];
}
else {
	$identifier = $config['global']['identifier'];
}

// generate flash vars
$flashVars = 'identifier=' . urlencode($identifier)
           . '&amp;api_key=' . urlencode($config['client']['api_key']);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Origo - social client</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Style-Type" content="text/css" />

		<meta name="robots" content="noindex, nofollow" />
		<meta name="keywords" content="origo, social client, foaf, semantic web, social network" />

		<meta name="description" content="Origo - social client - helps you in managing your social identity within the semantic web." />
		<meta name="copyright" content="copyright(c) 2008<?php if(date('Y') > 2008): echo '-' . date('Y'); endif; ?> Mario Volke. All right reserved." />
		<meta name="author" content="Mario Volke" />
		<meta name="dc.creator" content="Mario Volke" />
		<meta name="language" content="en" />
		<meta name="date" content="<?= date('c') ?>" />

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

