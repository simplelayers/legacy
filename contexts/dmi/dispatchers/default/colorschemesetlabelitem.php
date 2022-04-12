<?php
/**
  * Sets the default label field (labelitem) for the layer.
  * @package Dispatchers
  */
/**
  */
function _config_colorschemesetlabelitem() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemesetlabelitem($template, $args) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}

// set it and bail, simple!
$layer->labelitem = $_REQUEST['labelitem'];
print redirect("default.colorscheme&id={$layer->id}");


}?>
