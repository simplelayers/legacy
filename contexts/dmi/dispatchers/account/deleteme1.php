<?php
use subnav\SubnavFactory;
/**
 * The form for deleting your own account.
 * @package Dispatchers
 */
/**
  */
function _config_deleteme1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_deleteme1($template, $args) {
$user = $args['user'];
SubnavFactory::UseNav(SubnavFactory::SUBNAV_ACCOUNT,$template);
$template->assign('user',$user);
$template->display('account/deleteme1.tpl');

}?>
