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

// generate flash vars
$flashVars = 'identifier=' . urlencode($config['client']['resource'])
           . '&amp;endpoint=' . urlencode($config['endpoint']['location'])
           . '&amp;key=' . urlencode($config['endpoint']['key']);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title></title>
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

