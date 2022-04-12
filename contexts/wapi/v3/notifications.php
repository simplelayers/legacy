<?php
/**
 * Fetch a list of one's own layers.
 *
 * Parameters:
 *
 * (none)
 *
 * Return:
 *
 * XML representing the list of data layers, or else an error.
 * {@example docs/examples/wapi_error.txt}
 *
 * @package WebAPI
 */



/**
  * @ignore
  */
function _config_notifications() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}

function _headers_notifications() {
	header('Content-Type: application/json');
}

function _dispatch_notifications($template, $args) {
	$world = $args['world'];
	$user = $args['user'];
	echo $user->getAllNotificationsJson($_REQUEST["last"]);
}


?>
