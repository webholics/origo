<?php
/**
 * Base Url helper
 *
 * @author 		Mario Volke <mario.volke@webholics.de>
 * @package 	MV_View_Helper
 * @copyright 	(c) 2008 Mario Volke - http://www.webholics.de
 */
 
/**
 * @see Zend_Controller_Front
 */ 
require_once 'Zend/Controller/Front.php';

class MV_View_Helper_BaseUrl {
	
	/**
	 * Fetch the base url and append it to $path.
	 * If $path is empty return the base url.
	 *
	 * @param string $path
	 * @return string
	 */
	public function baseUrl($path = '')
	{
		return Zend_Controller_Front::getInstance()->getBaseUrl() . trim($path);
	}
}
