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
	
	// Index controller
	$router->addRoute('index', new Zend_Controller_Router_Route('/',
		array('controller' => 'index', 'action' => 'index')
	));
	$router->addRoute('terms', new Zend_Controller_Router_Route('terms/',
		array('controller' => 'index', 'action' => 'terms')
	));
	$router->addRoute('privacy', new Zend_Controller_Router_Route('privacy/',
		array('controller' => 'index', 'action' => 'privacy')
	));
	$router->addRoute('imprint', new Zend_Controller_Router_Route('imprint/',
		array('controller' => 'index', 'action' => 'imprint')
	));

	// Contact controller
	$router->addRoute('contact', new Zend_Controller_Router_Route('contact/',
		array('controller' => 'contact', 'action' => 'index')
	));

	// Invite friend controller
	$router->addRoute('invitefriend', new Zend_Controller_Router_Route('invite-friend/',
		array('controller' => 'invitefriend', 'action' => 'index')
	));
	$router->addRoute('invitefriend_addressbook', new Zend_Controller_Router_Route('invite-friend/address-book',
		array('controller' => 'invitefriend', 'action' => 'addressbook')
	));

	// Auth controller
	$router->addRoute('login', new Zend_Controller_Router_Route('login',
		array('controller' => 'auth', 'action' => 'login')
	));
	$router->addRoute('logout', new Zend_Controller_Router_Route('logout',
		array('controller' => 'auth', 'action' => 'logout')
	));
	$router->addRoute('forgotpw', new Zend_Controller_Router_Route('forgotpw/',
		array('controller' => 'auth', 'action' => 'forgotpw')
	));
	$router->addRoute('changepw', new Zend_Controller_Router_Route('changepw/:ac',
		array('controller' => 'auth', 'action' => 'changepw')
	));
	
	// Register controller
	$router->addRoute('register', new Zend_Controller_Router_Route('register/',
		array('controller' => 'register', 'action' => 'index')
	));
	$router->addRoute('activate', new Zend_Controller_Router_Route('register/activate/:ac',
		array('controller' => 'register', 'action' => 'activate')
	));
	$router->addRoute('resendactivation', new Zend_Controller_Router_Route('register/resend',
		array('controller' => 'register', 'action' => 'resendactivation')
	));

	// Profile controller
	$router->addRoute('profile', new Zend_Controller_Router_Route('profile/:name/*',
		array('controller' => 'profile', 'action' => 'profile'),
		array('page' => '\d+', 'commons-page' => '\d+')
	));
	$router->addRoute('myprofile', new Zend_Controller_Router_Route('profile/home',
		array('controller' => 'profile', 'action' => 'index')
	));
	$router->addRoute('myprofile_userimage', new Zend_Controller_Router_Route('profile/home/userimage',
		array('controller' => 'profile', 'action' => 'userimage')
	));
	$router->addRoute('myprofile_userimage_delete', new Zend_Controller_Router_Route('profile/home/userimage/delete',
		array('controller' => 'profile', 'action' => 'userimagedelete')
	));
	$router->addRoute('myprofile_userdata', new Zend_Controller_Router_Route('profile/home/userdata',
		array('controller' => 'profile', 'action' => 'userdata')
	));
	$router->addRoute('myprofile_changepassword', new Zend_Controller_Router_Route('profile/home/change-password',
		array('controller' => 'profile', 'action' => 'changepassword')
	));
	$router->addRoute('myprofile_settings', new Zend_Controller_Router_Route('profile/home/settings',
		array('controller' => 'profile', 'action' => 'settings')
	));

	// Image controller
	$router->addRoute('image_user', new Zend_Controller_Router_Route('image/user/:user_id/:size',
		array('controller' => 'image', 'action' => 'user', 'size' => 'big')
	));
	$router->addRoute('image_screenshot', new Zend_Controller_Router_Route('image/screenshot/:site_id',
		array('controller' => 'image', 'action' => 'screenshot')
	));

	// Site controller
	$router->addRoute('site', new Zend_Controller_Router_Route('site/:id/*',
		array('controller' => 'site', 'action' => 'site'),
		array('reader-page' => '\d+', 'comment-page' => '\d+')
	));
	$router->addRoute('site_add', new Zend_Controller_Router_Route('site/:id/add',
		array('controller' => 'site', 'action' => 'addsite'),
		array('page' => '\d+')
	));
	$router->addRoute('site_remove', new Zend_Controller_Router_Route('site/:id/remove',
		array('controller' => 'site', 'action' => 'removesite'),
		array('page' => '\d+')
	));
	$router->addRoute('site_sites', new Zend_Controller_Router_Route('sites/*',
		array('controller' => 'site', 'action' => 'index'),
		array('page' => '\d+')
	));
	$router->addRoute('site_insert', new Zend_Controller_Router_Route('sites/insert',
		array('controller' => 'site', 'action' => 'insert')
	));
	$router->addRoute('site_mysites', new Zend_Controller_Router_Route('sites/home/*',
		array('controller' => 'site', 'action' => 'mysites'),
		array('page' => '\d+')
	));
	$router->addRoute('sites_remove', new Zend_Controller_Router_Route('sites/remove',
		array('controller' => 'site', 'action' => 'remove')
	));

	// Readers controller
	$router->addRoute('readers', new Zend_Controller_Router_Route('readers/*',
		array('controller' => 'readers', 'action' => 'index'),
		array('page' => '\d+')
	));

	// Comment controller
	$router->addRoute('comment_remove', new Zend_Controller_Router_Route('comment/:comment_id/remove',
		array('controller' => 'comment', 'action' => 'remove')
	));
	$router->addRoute('comment_edit', new Zend_Controller_Router_Route('comment/:comment_id/edit',
		array('controller' => 'comment', 'action' => 'edit')
	));

	// Searcg controller
	$router->addRoute('search', new Zend_Controller_Router_Route('search',
		array('controller' => 'search', 'action' => 'index')
	));
	$router->addRoute('search_email', new Zend_Controller_Router_Route('search/email/:text/*',
		array('controller' => 'search', 'action' => 'email'),
		array('page' => '\d+')
	));
	$router->addRoute('search_user', new Zend_Controller_Router_Route('search/user/:text/*',
		array('controller' => 'search', 'action' => 'user'),
		array('page' => '\d+')
	));
	$router->addRoute('search_url', new Zend_Controller_Router_Route('search/url/:text/*',
		array('controller' => 'search', 'action' => 'url'),
		array('page' => '\d+')
	));
	$router->addRoute('search_name', new Zend_Controller_Router_Route('search/name/:text/*',
		array('controller' => 'search', 'action' => 'name'),
		array('page' => '\d+')
	));
	
	// Recommendation controller
	$router->addRoute('recommendation', new Zend_Controller_Router_Route('recommendation/*',
		array('controller' => 'recommendation', 'action' => 'index'),
		array('page' => '\d+', 'level' => '\d+')
	));
	
	unset($router);
?>
