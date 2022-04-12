<?php
/**
 * Process the vectorattributes form, and drop the specified column.
 * @package Dispatchers
 */
/**
 */
function _config_attributesdrop() {
	$config = Array ();
	// Start config
	// Stop config
	return $config;
}
function _dispatch_attributesdrop($template, $args) {
	$world = $args ['world'];
	$user = $args ['user'];
	
	// load the layer and verify their access
	$layer = $world->getLayerById ( $_REQUEST ['id'] );
	if (! $layer or $layer->getPermissionById ( $user->id ) < AccessLevels::EDIT) {
		print javascriptalert ( 'You do not have permission to edit that Layer.' );
		return print redirect ( 'layer.list' );
	}
	
	// drop the specified column(s) and send them back to the attributes page
	foreach ( $_REQUEST ['columns'] as $colname ) {
		if ($colname == 'gid')
			continue;
		if ($colname == 'the_geom')
			continue;
		$layer->dropAttribute ( $colname );
	}
	$layer->owner->notify ( $user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.attributes&id=" . $layer->id, 8 );
	print redirect ( "vector.attributes&id={$_REQUEST['id']}" );
}
?>
