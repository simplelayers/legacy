<?php
/**
 * Process the importshapefiles1 form, examining the zipfile and importing any shapefiles into new vector layers.
 * @package Dispatchers
 */
/**
  */
function _config_deletemetadata() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_deletemetadata($template, $args) {
$user = $args['user'];
$world = $args['world'];
$layer = $world->getLayerById($_REQUEST['id']);
$layer->clearMetadata();
return print redirect('layer.metadata1&id='.$_REQUEST['id']);
}?>
