<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

function _config_notify() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	// Stop config
	return $config;
}

function _headers_notify() {
	header('Content-Type: application/json');
}

function _dispatch_notify($template, $args) {
	$world = $args['world'];
	$user = $args['user'];
	
	switch(strtolower($_REQUEST['action'])){
		case "markall":
			$user->clearNotifications();
			break;
	}
}

?>