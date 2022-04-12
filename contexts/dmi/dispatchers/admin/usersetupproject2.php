<?php
/**
  * Administration: Process the adminusersetupproject1 form, saving the changes.
  * @package Dispatchers
  */
/**
  */
function _config_usersetupproject2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_usersetupproject2($template, $args) {
$world = $args['world'];

$world->setConfig('autobookmark_people',  $_REQUEST['friends'] );
$world->setConfig('autobookmark_layers',  $_REQUEST['bookmark_layers'] );
$world->setConfig('autobookmark_projects',$_REQUEST['bookmark_projects'] );
$world->setConfig('autocopy_layers',      $_REQUEST['copy_layers'] );
$world->setConfig('autocopy_projects',    $_REQUEST['copy_projects'] );
$world->setConfig('autoproject_basemaps', $_REQUEST['autoproject_basemaps'] );

print redirect('admin.userlist');

}?>
