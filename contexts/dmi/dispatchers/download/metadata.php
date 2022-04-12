<?php
ini_set('display_errors', 1);
/**
 * The HTML page for a utility to generate HTML iframe paragraphs.
 * @package Dispatchers
 */
/**
  */
function _config_metadata() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_metadata() {
}

function _dispatch_metadata($template, $args) {
	$world = System::Get();
	$user = SimpleSession::Get()->GetUser();
	$layer = $world->getLayerById($_REQUEST['id']);
        //header("Content-Description: File Transfer");
        //header("Content-Disposition: attachment; filename=".$layer->id.".xml");
	 header("Content-type: text/xml");
	$converter = new Convert();
	echo $converter->WritePhpToXml($layer->metadata,'metadata');
} 

?>