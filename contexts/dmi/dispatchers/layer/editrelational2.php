<?php
/**
 * Process the layereditvector1 form, to save their changes to the layer information.
 * @package Dispatchers
 */
/**
  */
function _config_editrelational2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_editrelational2($template, $args) {
$user = $args['user'];
$world = $args['world'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if ($layer->owner->id != $user->id) {
   print javascriptalert('Only the owner can edit this Layer.');
   return print redirect('layer.list');
}

// handle the simple attributes
$layer->name        = $user->uniqueLayerName($_REQUEST['name'],$layer);
$layer->description = $_REQUEST['description'];
$layer->tags    = $_REQUEST['tags'];
$layerid = $_REQUEST['id'];
// done -- keep them on the details page or send them to their layerbookmark list, depending
// on whether they own the layer they just edited
$layer->owner->notify($user->id, "edited layer:", $layer->name, $layer->id, "./?do=layer.info&id=".$layer->id, 5);

print redirect($layer->owner->id == $user->id ? 'layer.editrelational1&id='.$layerid : 'layer.bookmarks');

}?>
