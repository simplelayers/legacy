<?php
use utils\PageUtil;
/**
  * Administration: Login log.
  * @package Dispatchers
  */
/**
  */
function _config_loginlog() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_loginlog($template, $args,$org, $pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	$pageArgs['pageTitle'] = 'Admin - Account Login Logs';
	PageUtil::SetPageArgs($pageArgs, $template);
	$world = $args['world'];
	
	// fetch the specified number of entries from the logfile
	$howmany = @$_REQUEST['howmany']; if (!$howmany) $howmany = 100;
	$entries = $world->fetchRecentLogins($howmany);
	$template->assign('howmany',$howmany);
	$template->assign('entries',$entries);
	
	// the list of choices for how many records to show
	$template->assign('howmany_choices', array(50,100,200,300,500,1000) );
	$template->display('admin/loginlog.tpl');
	
}?>