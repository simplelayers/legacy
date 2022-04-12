<?php
use model\Permissions;
/**
 * Update records from the specified vector layer; called from vectorrecords.
 * @package Dispatchers
 */
/**
  */
function _config_recordupdate() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_recordupdate($template, $args,$org,$pageArgs) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);

if(!Permissions::HasPerm($pageArgs['permissions'],':Layers:General:',Permissions::EDIT)) {
    print javascriptalert('You do not have permission to edit layer Records.');
    return print redirect('layer.list');
}
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit this layer.');
   return print redirect('layer.list');
}

// go through each submitted record and do the update, easy
foreach ($_REQUEST['gids'] as $gid) {
   $layer->updateRecordById($gid, array($_REQUEST['column']=>$_REQUEST['value']) );
}

// done
$layer->owner->notify($user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.records&id=".$layer->id, 8);
print redirect("vector.records&id={$layer->id}");

}?>
