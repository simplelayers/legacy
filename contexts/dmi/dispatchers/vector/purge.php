<?php
/**
 * Delete all records from the specified vector layer. Use with care!
 * @package Dispatchers
 */
/**
  */
function _config_purge() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_purge($template, $args) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}

// purge it and send em to their now-empty list of records
$layer->truncate();
$layer->owner->notify($user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.records&id=".$layer->id, 8);
print redirect("vector.records&id={$layer->id}");
}?>