<?php
/**
 * Create a new entry in the layer's default color scheme, then forward the browser to the editing page for that new entry.
 * @package Dispatchers
 */
/**
  */
function _config_colorschemeadd() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemeadd($template, $args) {
$world = $args['world'];
$user = $args['user'];


// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}
$template->assign('layer',$layer);


// create the rule and update the layer's hint as to the color scheme type
$entry = $layer->colorscheme->addEntry();
$layer->colorschemetype = Colorschemes::MANUAL;

// send them to its editing view
print redirect("default.colorschemeedit1&id={$layer->id}&cid={$entry->id}");

}?>
