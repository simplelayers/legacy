<?php
/**
 * Remove the specified layer-bookmark; this is called from the layerinfo page.
 * @package Dispatchers
 */
/**
  */
function _config_removebookmark() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_removebookmark($template, $args) {
$world = $args['world'];
$user = $args['user'];

// if we're doing a single one, we may have come from layeredit or layerinfo
// use our permission level to decide which one it probably was
if (isset($_REQUEST['id'])) {
   $user->removeLayerBookmarkById($_REQUEST['id']);
   $layer = $world->getLayerByid($_REQUEST['id']);
   $permission = $layer->getPermissionById($user->id);

   if ($permission <= AccessLevels::NONE) return print redirect("layer.bookmarks");
   if ($permission >= AccessLevels::EDIT) return print redirect("layer.edit1&id={$_REQUEST['id']}");
   return print redirect("layer.info&id={$_REQUEST['id']}");
}

// otherwise, we're doing a list of bookmarks, and that's only from our bookmarks page
elseif (isset($_REQUEST['ids'])) {
   foreach ($_REQUEST['ids'] as $id) {
      $user->removeLayerBookmarkById($id);
   }
   print redirect('layer.bookmarks');
}


}?>
