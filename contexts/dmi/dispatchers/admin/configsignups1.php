<?php
use utils\PageUtil;
/**
  * Administration: the Configuration page.
  * @package Dispatchers
  */
/**
  */
function _config_configsignups1() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_configsignups1($template, $args,$org, $pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	PageUtil::SetPageArgs($pageArgs, $template);

	$world = $args['world'];

	// load the existing variables. this is easy!
	$template->assign('config',$world->config);

/*	$subnav = new AdminSubnav();
	$subnav->makeDefault($args["user"], "Welcome Messages",$org,$pageArgs );
	$template->assign('subnav',$subnav->fetch());
*/
	// and render it
	$template->display('admin/configsignups1.tpl');

}?>
