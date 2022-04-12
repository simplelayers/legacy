<?php
/**
 * Process the vectorattributes form to rename a column (aka attribute).
 * @package Dispatchers
 */
/**
  */
function _config_attributesrename() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_attributesrename($template, $args) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}

// the API handles the sanity checks, e.g. renaming to an already-existing column,
// cleaing up the column names, etc. So just try it and react based on the return code.
$result = $layer->renameAttribute($_REQUEST['oldcolumn'],$_REQUEST['newcolumn']);
if (!$result) print javascriptalert("Unable to rename the attribtue:\nEither an attribute by that name already exists,\nor the new name is invalid.");

// and done!
$layer->owner->notify($user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.attributes&id=".$layer->id, 8);
print redirect("vector.attributes&id={$_REQUEST['id']}");

}?>
