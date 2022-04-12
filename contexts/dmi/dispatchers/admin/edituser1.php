<?php

use enums\AccountTypes;
use utils\PageUtil;
/**
  * Administration: Form to edit a user.
  * @package Dispatchers
  */
/**
  */
function _config_edituser1() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_edituser1($template, $args,$org, $pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	PageUtil::SetPageArgs($pageArgs, $template);
	$world = $args['world'];
	
	// fetch the person, bail if they don't exist
	$person = $world->getPersonById($_REQUEST['id']);
	if (!$person) return print redirect('admin.userlist');
	$template->assign('person',$person);
	// their last login
	$lastlogin = $world->fetchRecentLogins(1,$person->username);
	$lastlogin = $lastlogin[0];
	$template->assign('lastlogin',$lastlogin);
	
	// the list of account types, so they can have it assigned manually
	#global $ACCOUNTTYPES;
	$accounttypes = AccountTypes::GetEnum()->ToOptionAssoc();
	$template->assign('accounttypes',$accounttypes);
	
	/*$subnav = new ContactSubnav();
	$subnav->makeDefault($person, $args["user"]);
	$template->assign('subnav',$subnav->fetch());
	*/
	// and send them to the editor
	$template->display('admin/edituser1.tpl');

}?>
