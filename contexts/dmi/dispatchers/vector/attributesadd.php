<?php
/**
 * Process the vectorattributes form to add a new column/attribute to the layer/table.
 * @package Dispatchers
 */
/**
  */
function _config_attributesadd() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_attributesadd($template, $args) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}

$ok = $layer->addAttribute($_REQUEST['name'],$_REQUEST['type']);
if (!$ok) print javascriptalert('The attribute you tried to create either already exists, is reserved by the system, or is invalid');
$layer->owner->notify($user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.attributes&id=".$layer->id, 8);
print redirect("vector.attributes&id={$_REQUEST['id']}");

}?>
