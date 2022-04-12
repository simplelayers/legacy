<?php

function _config_load() {
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

function _headers_load() {
	if(!isset($_REQUEST['format'] )) $_REQUEST['format'] = 'json';
	switch($_REQUEST['format'] ) {
		case "json":	
		case "ajax":
			header('Content-Type: application/json');
			break;
		case "xml":
			header('Content-Type: text/xml');
			break;
	}	
}

function _dispatch_load($template, $args) {
	$world = $args['world'];
	$layerId = $_REQUEST['layer'];
	$user = $args['user'];
	$wapi = System::GetWapi();
	switch ( isset($_REQUEST['project'] ) ) {
		case false:
			$layer =  $wapi->RequireLayer();
			$formatter = new LayerFormatter($args['world'],$user);
	
			break;
		case true:
			$layer = $wapi->RequireProjectLayer();
			$formatter = new ProjectLayerFormatter($args['world'],$user);
			break;
	}
	switch($wapi->format) {
		case WAPI::FORMAT_JSON:
			$formatter->WriteJSON($layer,$formatter->max);
			break;
		case WAPI::FORMAT_XML:
			$formatter->WriteXML($layer,$formatter->max);
			break;
	}
			
}


?>