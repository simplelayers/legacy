<?php
/**
  * Administration: Process the adminusersetuplayers1 form, saving the changes.
  * @package Dispatchers
  */
/**
  */
function _config_usersetuplayers2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_usersetuplayers2($template, $args) {
$world = $args['world'];

$world->setConfig('autocopy_layers', $_REQUEST['copy_layers'] );

$world->setConfig('defaultproject_name', $_REQUEST['project_name'] );
$world->setConfig('defaultproject_desc', $_REQUEST['project_desc'] );

print redirect('admin.userlist');

}?>
