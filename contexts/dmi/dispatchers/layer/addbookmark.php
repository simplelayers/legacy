<?php
/**
 * Add a layer to your bookmark list; this is called from the layerinfo page.
 * @package Dispatchers
 */
/**
  */
function _config_addbookmark() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_addbookmark($template, $args) {
$world = $args['world'];
$user = $args['user'];
// adding the bookmark is simple...
$user->addLayerBookmarkById($_REQUEST['id']);

// where to go from here? depends on their access level; maybe the edit view, maybe the info, ...
$layer = $world->getLayerByid($_REQUEST['id']);
$permission = $layer->getPermissionById($user->id);
if ($permission <= AccessLevels::NONE) return print redirect("layer.bookmarks");
if ($permission >= AccessLevels::EDIT) return print redirect("layer.edit1&id={$_REQUEST['id']}");
print redirect("layer.info&id={$_REQUEST['id']}");


}?>
