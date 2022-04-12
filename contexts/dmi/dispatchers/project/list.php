<?php
use utils\PageUtil;
use model\Permissions;
use utils\ParamUtil;
/**
 * Print a list of your projects, with links to edit them.
 * @package Dispatchers
 */
/**
  */
function _config_list() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_list($template, $args,$org,$pageArgs) {
	$pageArgs['pageSubnav'] = "maps";
	$pageArgs['pageTitle'] = 'Maps List';
	PageUtil::SetPageArgs($pageArgs, $template);
    PageUtil::MixinMapArgs($template);
    $showEdit = Permissions::HasPerm($pageArgs['permissions'],':MapsGeneral:',Permissions::VIEW);
    $showBookmarks = Permissions::HasPerm($pageArgs['permissions'],':MapsBookmarking:',Permissions::CREATE,Permissions::EDIT);
    if(!is_null(ParamUtil::Get($args,'groupId'))) {
        $template->assign('default',4);//group
        $template->assign('groupId',ParamUtil::Get($args,'groupId'));
    }
    $template->assign('isAdmin',$pageArgs['pageActor']=='admin');
    $template->assign('showBookmarks',$showBookmarks);
    $template->assign('showEdit',$showEdit);
	$template->display('project/list.tpl');
}?>