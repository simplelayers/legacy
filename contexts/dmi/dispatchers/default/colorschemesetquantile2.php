<?php
/**
 * Process the form generated by defaultcolorschemesetquantile2, to set the layer's default scheme.
 * @package Dispatchers
 */
/**
  */
function _config_colorschemesetquantile2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemesetquantile2($template, $args) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}

// setting them is easy!
$layer->colorscheme->setSchemeToQuantile($_REQUEST['column'],$_REQUEST['schemetype'],$_REQUEST['schemenumber'],$_REQUEST['schemename']);

print redirect("default.colorscheme&id={$layer->id}");

}?>