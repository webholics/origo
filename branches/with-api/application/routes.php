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
$router->addRoute('profile', new Zend_Controller_Router_Route('profile',
	array('controller' => 'profile', 'action' => 'index')
));
$router->addRoute('profile_rdf', new Zend_Controller_Router_Route('profile.rdf',
	array('controller' => 'profile', 'action' => 'rdf')
));
$router->addRoute('profile_turtle', new Zend_Controller_Router_Route('profile.turtle',
	array('controller' => 'profile', 'action' => 'turtle')
));
$router->addRoute('profile_json', new Zend_Controller_Router_Route('profile.json',
	array('controller' => 'profile', 'action' => 'json')
));
$router->addRoute('profile_html', new Zend_Controller_Router_Route('profile.html',
	array('controller' => 'profile', 'action' => 'html')
));

// ClientController
$router->addRoute('client', new Zend_Controller_Router_Route('client',
	array('controller' => 'client', 'action' => 'index')
));

// ApiController
$router->addRoute('api_test', new Zend_Controller_Router_Route('api/test',
	array('controller' => 'api', 'action' => 'test')
));

// EditorApiController
$router->addRoute('api_editor_get', new Zend_Controller_Router_Route('api/editor/get',
	array('controller' => 'editorApi', 'action' => 'get')
));
$router->addRoute('api_editor_update', new Zend_Controller_Router_Route('api/editor/update',
	array('controller' => 'editorApi', 'action' => 'update')
));
$router->addRoute('api_editor_delete', new Zend_Controller_Router_Route('api/editor/delete',
	array('controller' => 'editorApi', 'action' => 'delete')
));
$router->addRoute('api_editor_clean', new Zend_Controller_Router_Route('api/editor/clean',
	array('controller' => 'editorApi', 'action' => 'clean')
));
$router->addRoute('api_editor_profiles_get', new Zend_Controller_Router_Route('api/editor/profiles/get',
	array('controller' => 'editorApi', 'action' => 'profilesget')
));
$router->addRoute('api_editor_profiles_update', new Zend_Controller_Router_Route('api/editor/profiles/update',
	array('controller' => 'editorApi', 'action' => 'profilesupdate')
));
$router->addRoute('api_editor_profiles_delete', new Zend_Controller_Router_Route('api/editor/profiles/delete',
	array('controller' => 'editorApi', 'action' => 'profilesdelete')
));
$router->addRoute('api_editor_relationships_get', new Zend_Controller_Router_Route('api/editor/relationships/get',
	array('controller' => 'editorApi', 'action' => 'relationshipsget')
));
$router->addRoute('api_editor_relationships_update', new Zend_Controller_Router_Route('api/editor/relationships/update',
	array('controller' => 'editorApi', 'action' => 'relationshipsupdate')
));
$router->addRoute('api_editor_relationships_delete', new Zend_Controller_Router_Route('api/editor/relationships/delete',
	array('controller' => 'editorApi', 'action' => 'relationshipsdelete')
));

// BrowserApiController
$router->addRoute('api_browser_profile', new Zend_Controller_Router_Route('api/browser/profile',
	array('controller' => 'browserApi', 'action' => 'profile')
));
$router->addRoute('api_browser_relationships', new Zend_Controller_Router_Route('api/browser/relationships',
	array('controller' => 'browserApi', 'action' => 'relationships')
));
$router->addRoute('api_browser_clean', new Zend_Controller_Router_Route('api/browser/clean',
	array('controller' => 'browserApi', 'action' => 'clean')
));

unset($router);
