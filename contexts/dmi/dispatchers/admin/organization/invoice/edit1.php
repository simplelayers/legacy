<?php
use subnav\OrganizationSubnav;

/**
 * The form for changing your password.
 * @package Dispatchers
 */
/**
  */
  error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
function _config_edit1() {
	$config = Array();
	// Start config
	$config["admin"] = false;
	// Stop config
	return $config;
}

function _dispatch_edit1($template, $args) {
	$invoice = $args["world"]->getInvoiceById($_REQUEST["id"]);
	$org = $invoice->org;
	$subnav = new OrganizationSubnav();	
$subnav->makeDefault($args["user"],'',$org);

$template->assign('subnav',$subnav->fetch());
	$template->assign('org', $org);
	$template->assign('invoice', $invoice);
	$template->assign('radio', true);
	$template->display('admin/organization/invoice/edit1.tpl');

}?>
