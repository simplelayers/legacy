<?php
/**
 * Process the importshapefiles1 form, examining the zipfile and importing any shapefiles into new vector layers.
 * @package Dispatchers
 */
/**
  */
function _config_metadata2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_metadata2($template, $args) {
$user = $args['user'];
$world = $args['world'];
$layer = $world->getLayerById($_REQUEST['id']);
$layer->importMetadata($_FILES["source"]['tmp_name']);
return print redirect('layer.metadata1&id='.$_REQUEST['id']);
}?>
