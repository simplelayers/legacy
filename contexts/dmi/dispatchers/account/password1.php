<?php
use subnav\SubnavFactory;
/**
 * The form for changing your password.
 * @package Dispatchers
 */
/**
  */
function _config_password1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_password1($template, $args) {
$user = $args['user'];

SubnavFactory::UseNav(SubnavFactory::SUBNAV_ACCOUNT,$template);
$template->assign('user',$user);
$template->display('account/password1.tpl');

}?>
