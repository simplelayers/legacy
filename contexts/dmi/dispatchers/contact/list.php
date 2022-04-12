<?php
use utils\PageUtil;
use model\Permissions;
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
	
$user = $args['user'];
$world = $args['world'];

$pageArgs['pageSubnav'] = 'community';
$pageArgs['pageTitle'] = 'Contacts - List';

$isSysAdmin = Permissions::HasPerm($pageArgs['permissions'],':SysAdmin:General:', Permissions::VIEW);

PageUtil::SetPageArgs($pageArgs, $template);

$template->assign('isSys',$isSysAdmin);
if(isset($_REQUEST["tag"])){
	$template->assign('tag', $_REQUEST["tag"]);
}else{
	$template->assign('tag', false);
}

$template->display('contact/list.tpl');

}?>
