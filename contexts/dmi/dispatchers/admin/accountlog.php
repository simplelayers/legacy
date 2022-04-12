<?php
use utils\PageUtil;
/**
  * Administration: Account activity log.
  * @package Dispatchers
  */
/**
  */
function _config_accountlog() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_accountlog($template, $args,$org,$pageArgs ) {
$world = $args['world'];

// fetch the specified number of entries from the logfile
$howmany = @$_REQUEST['howmany'];
 if (!$howmany) $howmany = 100;
$entries = $world->fetchAccountActivity($howmany);
$template->assign('howmany',$howmany);
$template->assign('entries',$entries);

// the list of choices for how many records to show
$template->assign('howmany_choices', array(50,100,200,300,500,1000) );
$pageArgs['pageSubnav'] = 'admin';
$pageArgs['pageTitle'] = 'Admin - Account Log';

/*	$subnav = new AdminSubnav();
	$subnav->makeDefault($args["user"], "User Account Changes Log",$org,$pageArgs );
	$template->assign('subnav',$subnav->fetch());
*/
PageUtil::SetPageArgs($pageArgs, $template);
// and send it off for rendering
$template->display('admin/accountlog.tpl');

}?>