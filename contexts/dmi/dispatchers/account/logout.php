<?php
use auth\Context;
/**
 * Log the user out (delete their stored authentication) and then send them to the login page.
 * @package Dispatchers
 */
/**
  */
function _config_logout() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_logout($template, $args) {
/* @var $_SESSION SimpleSession */
	unset($_REQUEST['username']);
	unset($_REQUEST['password']);
	$err = error_reporting();
	error_reporting(0);
	
	$context = Context::Get();
	$_SESSION->EndSession();
	print redirect();
	error_reporting($err);	
	
	
	#$context->EndSession();
	//





}?>
