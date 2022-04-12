<?php
/**
  * Administration: Process the adminusersetupfriends1 form, saving the changes.
  * @package Dispatchers
  */
/**
  */
function _config_usersetupfriends2() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["admin"] = true;
	// Stop config
	return $config;
}

function _dispatch_usersetupfriends2($template, $args) {
$world = $args['world'];

$world->setConfig( 'autobookmark_people',  $_REQUEST['friends']  );

print redirect('admin.userlist');

}?>
