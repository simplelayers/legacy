<?php
use utils\PageUtil;
use utils\ParamUtil;
/**
 * Print a list of all people on your buddy list, with links to email them, remove them, etc.
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
	
	$pageArgs['pageSubnav'] = 'community';
	$pageArgs['pageTitle'] = 'Community - Groups List';
	$pageArgs['groupId'] = ParamUtil::Get($args,'groupId');
	PageUtil::SetPageArgs($pageArgs,$template);
	$user = $args['user'];
	$world = $args['world'];

	if(isset($_REQUEST["tag"])){
		$template->assign('tag', $_REQUEST["tag"]);
	}else{
		$template->assign('tag', false);
	}
	$template->display('group/list.tpl');

}?>