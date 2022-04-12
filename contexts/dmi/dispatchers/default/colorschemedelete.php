<?php
/**
 * Delete an entry from the layer's default color scheme.
 * @package Dispatchers
 */
/**
  */
function _config_colorschemedelete() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemedelete($template, $args) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}

// fetch the rule, and delete it
$entry = $layer->colorscheme->getEntryByid($_REQUEST['cid']);
if ($entry) $entry->delete();

// update the layer's hint as to the color scheme type
$layer->colorschemetype = Colorschemes::MANUAL;

// easy, huh?
print redirect("default.colorscheme&id={$layer->id}");


}?>
