<?php
use subnav\AdminSubnav;
/**
 * The form for changing your password.
 * @package Dispatchers
 */
/**
  */
function _config_new1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_new1($template, $args) {
	$template->assign('radio', true);
	$subnav = new AdminSubnav();
	$subnav->makeDefault($args["user"], "New Organization");
	$template->assign('subnav',$subnav->fetch());
	$template->display('admin/organization/new1.tpl');

}?>
