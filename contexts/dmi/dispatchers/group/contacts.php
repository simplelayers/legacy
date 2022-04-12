<?php
use utils\PageUtil;

function _config_contacts() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_contacts($template, $args, $org, $pageArgs) {
    
	$world = $args["world"];
	$user = $args["user"];
	$group = $world->getGroupById($_REQUEST['groupId']);
	$pageArgs = PageUtil::MixinGroupArgs($template);
	
	$pageArgs['pageSubnav'] = 'community';
	$pageArgs['inviteOnly'] = true;
	$pageArgs['groupName'] = $group->title;
	$pageArgs['pageTitle'] = $group->title."'s Contacts";
	
	
	PageUtil::SetPageArgs($pageArgs, $template);
	$template->assign('group', $group);
	/*$subnav = new GroupSubnav();
	$subnav->makeDefault($group, $user);
	$template->assign('subnav',$subnav->fetch());
	*/
	if(isset($_REQUEST["tag"])){
	    $template->assign('tag', $_REQUEST["tag"]);
	}else{
	    $template->assign('tag', false);
	}
	$template->assign('inviteonly',true);
	$template->display('group/contacts.tpl');
}?>