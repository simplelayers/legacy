<?php
use utils\PageUtil;
/**
  * Administration: Set some default settings for new users: their starting Friends list.
  * @package Dispatchers
  */
/**
  */
function _config_usersetupfriends1() {
	$config = Array();
	// Start config
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_usersetupfriends1($template, $args,$org, $pageArgs ) {
	$pageArgs['pageSubnav'] = 'admin';
	PageUtil::SetPageArgs($pageArgs, $template);
	$world = $args['world'];
	
	$people = array();
	$already = explode(',',$world->config['autobookmark_people']);
	foreach ($world->getAllPeople() as $p) {
	   $label     = sprintf("%s%s%s", $p->username, str_repeat('&nbsp;',21-strlen($p->username)), $p->realname);
	   $selected  = in_array($p->id,$already) ? 'selected="true"' : '';
	   $people[$p->id] = array('label'=>$label,'selected'=>$selected);
	}
	$template->assign('people',$people);
	
	/*$subnav = new AdminSubnav();
	$subnav->makeDefault($args["user"], "Default Bookmarked Contacts",$org,$pageArgs );
	$template->assign('subnav',$subnav->fetch());
	*/
	
	// and send them to the editor
	$template->display('admin/usersetupfriends1.tpl');

}?>
