<?php
/**
* Origo - social client
* Bootstrap
*
* @copyright (C) 2008-2009 Mario Volke, All right reserved.
* @author Mario Volke (mario.volke@webholics.de)
*/

if(!is_file(realpath(dirname(__FILE__)) . '/application/startup.php'))
	die('Origo error: startup.php not found!');

require_once realpath(dirname(__FILE__)) . '/application/startup.php';

try {
	$config = Zend_Registry::get('config');

	$frontController = Zend_Controller_Front::getInstance();
	$frontController->setControllerDirectory(APPLICATION_PATH . '/controllers');
	$frontController->setParam('env', $config->misc->environment);

	// set custom routes
	require_once(APPLICATION_PATH . '/routes.php');

	if($config->misc->environment == 'development') {
		$frontController->throwExceptions(false);
		$frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
	}
	
	unset($frontController, $config);
} 
//non-Controller exceptions should be displayed nicely though
catch (Exception $exception) {
    echo '<html><body><center>'
       . 'An exception occured ';
    if (defined('APPLICATION_ENVIRONMENT')
        && APPLICATION_ENVIRONMENT != 'production'
    ) {
        echo '<br /><br />' . $exception->getMessage() . '<br />'
           . '<div align="left">Stack Trace:' 
           . '<pre>' . $exception->getTraceAsString() . '</pre></div>';
    }
    echo '</center></body></html>';
    exit(1);
}

Zend_Controller_Front::getInstance()->dispatch();
	
