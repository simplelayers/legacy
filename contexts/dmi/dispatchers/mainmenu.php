<?php
/**
  * The action called after logging in; all it does is redirect the user to the projectlist action.
  * @package Dispatchers
  */
/**
  */
function _config_mainmenu() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	$config["authUser"] = 0;
	// Stop config
	return $config;
}

function _dispatch_mainmenu($template, $args) {
if((!isset($args["user"])) || (!$args["user"])){
	print redirect('account.login');
}else{
	print redirect('project.list');
}

}?>
