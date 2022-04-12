<?php
use subnav\OrganizationSubnav;

/**
 * The form for changing your password.
 * @package Dispatchers
 */
/**
  */
function _config_edit1() {
	$config = Array();
	// Start config
	$config["admin"] = false;
	// Stop config
	return $config;
}

function _dispatch_edit1($template, $args) {
	$org = $args["world"]->getOrganizationById($_REQUEST["id"]);
	$subnav = new OrganizationSubnav();
$subnav->makeDefault($args["user"],'',$org);
$template->assign('subnav',$subnav->fetch());
	$template->assign('org', $org);
	$template->assign('radio', true);
	$template->display('admin/organization/edit1.tpl');

}?>
