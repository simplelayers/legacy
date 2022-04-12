<?php
use subnav\AdminSubnav;
/**
 * Print a list of your data layers, with links to edit them, set permissions, etc.
 * @package Dispatchers
 */
/**
  */
function _config_list() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_list($template, $args) {
$template->assign("select", false);

$subnav = new AdminSubnav();
$subnav->makeDefault($args["user"], "Organizations List");
$template->assign('subnav',$subnav->fetch());

$template->display('admin/organization/list.tpl');
}?>