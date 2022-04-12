<?php
/**
 * Process the vectorrecordedit1 form, to save the updates to the record.
 * @package Dispatchers
 */
/**
  */
function _config_recordedit2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_recordedit2($template, $args) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}

// load the record, ensure that it exists
$record = $layer->getRecordById($_REQUEST['gid']);
if (!$record) {
   print javascriptalert('That record does not exist.');
   return print redirect("vector.records&id={$layer->id}");
}

// compile the changes into an array suitable for updateRecordById()
$changes = array();
foreach ($_REQUEST as $k=>$value) {
   if(preg_match('/^column_(\w+)$/',$k,$column)) $column = @$column[1]; if (!$column) continue;
   if ($value == '') $value = null; // if the value is blank, null is properly cast as NULL but '' is an invalid value for numeric types
   $changes[$column] = $value;
}
$layer->updateRecordById($_REQUEST['gid'],$changes);
$layer->owner->notify($user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.records&id=".$layer->id, 8);
print redirect("vector.records&id={$layer->id}");

}?>
