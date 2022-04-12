<?php
/**
 * @package Dispatchers
 */
/**
  */
function _config_is_unique() {
	$config = Array();
	// Start config
	$config["header"] = false;
    $config["footer"] = false;
    $config["customHeaders"] = true;
	// Stop config
	return $config;
}


function _headers_is_unique() {
	//header('Content-type: application/json');//?foo
}



function _dispatch_is_unique($template, $args) {

	$user = $args['user'];
	$world = $args['world'];
	$name=$_REQUEST['name'];
 
	$desiredname=$name;

	$layerexist = $user->layerExists($desiredname);
	echo json_encode(array("response"=>$layerexist));
}?>
