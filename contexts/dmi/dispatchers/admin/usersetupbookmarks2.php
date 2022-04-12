<?php
/**
  * Administration: Process the adminusersetupbookmarks1 form, saving the changes.
  * @package Dispatchers
  */
/**
  */
function _config_usersetupbookmarks2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_usersetupbookmarks2($template, $args) {
$world = $args['world'];

$world->setConfig('autobookmark_layers',  $_REQUEST['bookmark_layers'] );
$world->setConfig('autobookmark_projects',$_REQUEST['bookmark_projects'] );

print redirect('admin.userlist');

}?>
