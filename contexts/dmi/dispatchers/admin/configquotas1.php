<?php

use utils\PageUtil;
/**
  * Administration: the Configuration page.
  * @package Dispatchers
  */
/**
  */
function _config_configquotas1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_configquotas1($template, $args,$org,$pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	PageUtil::SetPageArgs($pageArgs, $template);
$world = $args['world'];

// load the existing variables. this is easy!
$template->assign('config',$world->config);

// make a list of account types and prices
global $ACCOUNTTYPES;
$template->assign('accounttypes',$ACCOUNTTYPES);

/*	$subnav = new AdminSubnav();
	$subnav->makeDefault($args["user"], "Quotas and Pricing",$org,$pageArgs );
	$template->assign('subnav',$subnav->fetch());
*/

// and render it
$template->display('admin/configquotas1.tpl');
}?>