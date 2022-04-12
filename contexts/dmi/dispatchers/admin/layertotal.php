<?php
use utils\PageUtil;
/**
 * Administration: Process the adminusersetupproject1 form, saving the changes.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_layertotal() {
	$config = Array ();
	// Start config
	$config ["sendUser"] = false;
	$config ["admin"] = true;
	// Stop config
	return $config;
}
function _dispatch_layertotal($template, $args, $org, $pageArgs) {
	$pageArgs ['pageSubnav'] = 'admin';
	PageUtil::SetPageArgs ( $pageArgs, $template );
	$world = $args ['world'];
	$total = 0;
	foreach ( $world->searchLayers () as $layer ) {
		$total += @$layer->diskusage;
	}
	
	print $total;
}
?>
