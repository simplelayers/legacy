<?php
use utils\PageUtil;
/**
  * Administration: Form to create a new user.
  * @package Dispatchers
  */
/**
  */
function _config_adduser1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_adduser1($template, $args,$org,$pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	PageUtil::SetPageArgs($pageArgs, $template);
/*$subnav = new AdminSubnav();
$subnav->makeDefault($args["user"], "New User Account",$org,$pageArgs );
$template->assign('subnav',$subnav->fetch());
*/

	
$template->display('admin/adduser1.tpl');

}?>
