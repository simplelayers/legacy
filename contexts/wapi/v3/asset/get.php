<?php

function _config_get() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	$config['sendUser'] = true;
	$config['sendWorld'] = true;
	
	// Stop config
	return $config;
}

function _headers_get() {
	
	if(!isset($_REQUEST['format'] )) $_REQUEST['format'] = 'json';
	switch($_REQUEST['format'] ) {
		case "swf":
			header("Content-Type: application/octet-stream");
			break;
		case "png":
			header('Content-type: image/png',true);
			break;
		case "jpg":
			header('Content-type: image/jpeg',true);
		case "text":
			header('Content-type: text/javascript', true);
	}	
}
function _dispatch_get($template, $args) {
	$user = $args['user'];
	$ini = System::GetIni();
	$asset = $_REQUEST['asset'];
	_headers_get();
	readfile($ini->tempdir.$asset);
	
			
}





?>