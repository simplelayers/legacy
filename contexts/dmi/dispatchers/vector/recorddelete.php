<?php
/**
 * Delete records from the specified vector layer; called from vectorrecords.
 * @package Dispatchers
 */
/**
  */
function _config_recorddelete() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_recorddelete($template, $args) {
$world = System::Get();
$user = SimpleSession::Get()->GetUser();

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}

// go through each submitted record and do the delete, easy
foreach ($_REQUEST['gids'] as $gid) {
   $layer->deleteRecordById($gid);
}
$layer->owner->notify($user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.records&id=".$layer->id, 8);
print redirect("vector.records&id={$layer->id}");
}?>