<?php
use subnav\AdminSubnav;
/**
 * Print a list of your projects, with links to edit them.
 * @package Dispatchers
 */
/**
  */
  
function _config_list() {
	$config = Array();
	// Start config
	$config["admin"] = false;
	// Stop config
	return $config;
}

function _dispatch_list($template, $args) {
$user = $args['user'];
$world = $args['world'];

$template->assign('org',false);

$subnav = new AdminSubnav();

$subnav->makeDefault($args["user"], "Organizations List");
$template->assign('subnav',$subnav->fetch());

$template->display('admin/organization/invoice/list.tpl');
}?>