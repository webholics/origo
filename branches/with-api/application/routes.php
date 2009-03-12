<?php
/**
* Origo - social client
* routing definitions
*
* @copyright (C) 2008-2009 Mario Volke, All right reserved.
* @author Mario Volke (mario.volke@webholics.de)
*/

$router = Zend_Controller_Front::getInstance()->getRouter(); // returns a rewrite router by default
$router->removeDefaultRoutes();

// ProfileController
$router->addRoute('profile', new Zend_Controller_Router_Route('/',
	array('controller' => 'profile', 'action' => 'index')
));
$router->addRoute('profile_rdf', new Zend_Controller_Router_Route('rdf',
	array('controller' => 'profile', 'action' => 'rdf')
));

unset($router);
