<?php

use subnav\SubnavFactory;
use enums\AccountTypes;
/**
 * Display your account type, disk usage, expiration date, and links to upgrade those.
 * @package Dispatchers
 */
/**
  */
function _config_type() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_type($template, $args) {
$user = $args['user'];
// get their account type and stuff
$accountTypes = AccountTypes::GetEnum();

$template->assign('accounttype',$accountTypes[$user->accounttype]);
$template->assign('maxAccount',AccountTypes::MAX);
$template->assign('user', $user);

SubnavFactory::UseNav(SubnavFactory::SUBNAV_ACCOUNT,$template);

// fetch the list of their layers, for the disk usage report
function by_size($a,$b) { return $a->diskusage < $b->diskusage; }
$layers = $user->listLayers();
usort($layers,'by_size');
$template->assign('layers',$layers);

// and off to the HTML
$template->display('account/type.tpl');

}?>
