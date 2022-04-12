<?php
use subnav\OrganizationSubnav;
use utils\PageUtil;

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

function _dispatch_list($template, $args, $org, $pageArgs) {
	$pageArgs ['pageSubnav'] = 'org';
$user = $args['user'];
$world = $args['world'];

$org = $world->getOrganizationById($_REQUEST['id']);
if (!$org) {
   print javascriptalert('That organization was not found, or is unlisted.');
   return print redirect('organization.list');
}
if($org->owner->id != $args['user']->id and !$user->admin){
	print javascriptalert('You are not the owner of this organization.');
	return print redirect('organization.info&id='.$org->id);
}
$template->assign('org',$org);
$subnav = new OrganizationSubnav();
$subnav->makeDefault( $user,'',$org);
$template->assign('subnav',$subnav->fetch());
PageUtil::SetPageArgs($pageArgs, $template);
$template->display('organization/invoice/list.tpl');
}?>