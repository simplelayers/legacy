<?php
/**
 * The "Forgot Your Password"s subsystem -- The form for entering the username.
 * @package Dispatchers
 */
/**
  */
function _config_forgotpassword1() {
	$config = Array();
	// Start config
	$config["authUser"] = 0;
	$config['css_url'] = "login.css";
	// Stop config
	return $config;
}

function _dispatch_forgotpassword1($template, $args) {
	unset($_REQUEST['world']);


$template->display('account/forgotpassword1.tpl');

}?>