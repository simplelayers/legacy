<?php
/**
 * The "Search Community" subsystem -- the main search page.
 * @package Dispatchers
 */
/**
  */
function _config_search() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["sendWorld"] = false;
	$config["authUser"] = 0;
	// Stop config
	return $config;
}

function _dispatch_search($template, $args) {

$template->display('demo/search.tpl');

}?>
