<?php
use utils\PageUtil;
function _config_projects() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_projects($template, $args,$org,$pageArgs) {
	$group = $_REQUEST['id'];
	$world = $args["world"];
	$user = $args["user"];
	$template->assign('selector', 'true');
	$template->assign('group', $group);
	$group = $world->getGroupById($_REQUEST['groupId']);
	$pageArgs['pageSubnav'] = 'community';
	$pageArgs['groupId'] = $group->id;
	$pageArgs['groupName'] = $group->title;
	$pageArgs['groupOrg'] = $group->org_id;
	$pageArgs['pageTitle'] = 'Community - Maps for group '.$group->title;
	
	PageUtil::SetPageArgs($pageArgs, $template);
	
	/*$subnav = new GroupSubnav();
	$subnav = new GroupSubnav();
	$subnav->makeDefault($group, $user);
	$template->assign('subnav',$subnav->fetch());
	*/
	$template->display('group/projects.tpl');
}?>