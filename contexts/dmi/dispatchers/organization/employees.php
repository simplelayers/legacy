<?php
use subnav\OrganizationSubnav;
use utils\PageUtil;


function _config_employees() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_employees($template, $args, $org, $pageArgs) {
	$pageArgs ['pageSubnav'] = 'org';
$user = $args['user'];
$world = $args['world'];

$org = $world->getOrganizationById($_REQUEST['id']);
if (!$org) {
   print javascriptalert('That organization was not found, or is unlisted.');
   return print redirect('organization.list');
}
if($org->owner->id != $args['user']->id){
	print javascriptalert('You are not the owner of this organization.');
	return print redirect('organization.info&id='.$org->id);
}
$template->assign('org',$org);
$template->assign('group',$org->group);

$subnav = new OrganizationSubnav();
$subnav->makeDefault($user,'',$org);
$template->assign('subnav',$subnav->fetch());
$template->assign('selector', 'true');
PageUtil::SetPageArgs($pageArgs, $template);
$template->display('organization/employees.tpl');
}?>