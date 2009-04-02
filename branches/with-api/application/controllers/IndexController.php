<?php
/**
* Origo - social client
* IndexController
*
* @copyright Copyright (c) 2008-2009 Mario Volke
* @author    Mario Volke <mario.volke@webholics.de>
*/

class IndexController extends Zend_Controller_Action
{
	/**
	 * This action makes a 303 redirect to the profile.
	 */
	public function indexAction()
	{
		$this->_helper->Redirector
			->setCode(303)
			->setUseAbsoluteUri()
			->setGotoRoute(array(), 'profile');
	}
}
