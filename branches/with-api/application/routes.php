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
$router->addRoute('profile_turtle', new Zend_Controller_Router_Route('ttl',
	array('controller' => 'profile', 'action' => 'turtle')
));
$router->addRoute('profile_json', new Zend_Controller_Router_Route('json',
	array('controller' => 'profile', 'action' => 'json')
));
$router->addRoute('profile_html', new Zend_Controller_Router_Route('html',
	array('controller' => 'profile', 'action' => 'html')
));

unset($router);
