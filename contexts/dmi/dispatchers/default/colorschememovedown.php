<?php
/**
 * Given a color scheme entry's ID#, increase its priority to move it down the list.
 * @package Dispatchers
 */
/**
  */
function _config_colorschememovedown() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschememovedown($template, $args) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}


// fetch the rule, and assign its new priority
$entry = $layer->colorscheme->getEntryByid($_REQUEST['cid']);
if ($entry) $entry->priority += 1;

// easy, huh?
print redirect("default.colorscheme&id={$layer->id}");

}?>
