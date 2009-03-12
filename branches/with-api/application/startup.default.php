<?php
/**
* Origo - social client
* startup, initialize php environment and define globals
*
* @copyright (C) 2008-2009 Mario Volke, All right reserved.
* @author Mario Volke (mario.volke@webholics.de)
*/

// unfortunately ARC2 does not yet support E_STRICT
//error_reporting(E_ALL|E_STRICT);
error_reporting(E_ALL);
	
// APPLICATION_PATH is a constant pointing to our
// application/subdirectory. We use this to add our "library" directory
// to the include_path, so that PHP can find our Zend Framework classes.
define('APPLICATION_PATH', realpath(dirname(__FILE__)));

set_include_path(
	APPLICATION_PATH . '[path to Zend Framework Library]' 
	. PATH_SEPARATOR . APPLICATION_BASEPATH . '/../library/'
	. PATH_SEPARATOR . get_include_path()
);

require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

$registry = Zend_Registry::getInstance();

if(!is_file(APPLICATION_PATH . '/../config/config.ini'))
	die('Origo error: config/config.ini not found!');

$config = new Zend_Config_Ini(APPLICATION_PATH . '/../config/config.ini');
$registry->config = $config;

//$dbAdapter = Zend_Db::factory($config->database);
//Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
//$registry->dbAdapter = $dbAdapter;

//date_default_timezone_set($config->timezone);

// should be disabled on public servers
if($config->misc->environment == 'development') {
	ini_set('display_errors', 1);
}
else {
	ini_set('display_errors', 0);
}

if(!is_writable(APPLICATION_PATH . '/../' . $config->cache->dir)) {
	die('Origo error: Cache directory is not writable.');
}

unset($config, $registry, $dbAdapter);
