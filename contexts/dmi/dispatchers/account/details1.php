<?php
/**
 * Form for allowing the person to change their personal details: name, contact info, etc.
 * @package Dispatchers
 */
/**
  */
function _config_details1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_details1($template, $args) {
$user = $args['user'];

$template->assign('user',$user);
$template->display('account/details1.tpl');

}?>
