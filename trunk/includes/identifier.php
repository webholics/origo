<?php
/**
* Origo - social client
*
* Copyright (C) 2008 Mario Volke
* All right reserved.
*/

/**
 * Construct the personal URI.
 * @param $config The config array loaded from the ini config file.
 * @return the personal URI
 */
function identifier($config) {
	// check if identifier is a hash
	if($config['global']['identifier'][0] == '#') {
		return $config['global']['document_uri'] . $config['global']['identifier'];
	}

	return $config['global']['identifier'];
}

