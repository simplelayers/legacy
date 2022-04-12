<?php
use subnav\GroupSubnav;
use utils\PageUtil;
function _config_layers() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_layers($template, $args,$org,$pageArgs) {
	$group = $_REQUEST['id'];
	$world = $args["world"];
	$user = $args["user"];
	$template->assign('selector', 'true');
	$template->assign('group', $group);
	$group = $world->getGroupById($_REQUEST['groupId']);
	
	$pageArgs['pageSubnav'] = 'community';
	$pageArgs['pageTitle'] = 'Community - Layers for group '.$group->title;
	$pageArgs['groupId'] = $group->id;
	$pageArgs['groupName'] = $group->title;
	$pageArgs['groupOrg'] = $group->org_id;
	
	
	PageUtil::SetPageArgs($pageArgs, $template);
	$subnav = new GroupSubnav();
	/*$subnav->makeDefault($group, $user);
	$template->assign('subnav',$subnav->fetch());
	*/
	$template->display('group/layers.tpl');
}?>