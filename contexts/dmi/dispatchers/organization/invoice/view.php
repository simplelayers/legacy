<?php
use utils\PageUtil;
/**
 * Print a list of your projects, with links to edit them.
 * @package Dispatchers
 */
/**
  */
  
function _config_view() {
	$config = Array();
	// Start config
	$config['print_css'] = "noheadprint.css";
	$config['css_url'] = "invoice.css";
	// Stop config
	return $config;
}

function _dispatch_view($template, $args, $org, $pageArgs) {
	$pageArgs ['pageSubnav'] = 'org';
$user = $args['user'];
$world = $args['world'];

$invoice = $world->getInvoiceById($_REQUEST['id']);
$org = $invoice->org;
if (!$org) {
   print javascriptalert('That organization was not found, or is unlisted.');
   return print redirect('organization.list');
}
if($org->owner->id != $args['user']->id and !$user->admin){
	print javascriptalert('You are not the owner of this organization.');
	return print redirect('organization.info&id='.$org->id);
}
$template->assign('org',$org);
$template->assign('user',$user);
$template->assign('invoice',$invoice);
/*$subnav = new OrganizationSubnav();
$subnav->makeDefault($user,'',$org);
$template->assign('subnav',$subnav->fetch());
*/
PageUtil::SetPageArgs($pageArgs, $template);
$template->display('organization/invoice/view.tpl');
}?>