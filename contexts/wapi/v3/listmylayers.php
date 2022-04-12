<?php
/**
 * Fetch a list of one's own layers.
 *
 * Parameters:
 *
 * (none)
 *
 * Return:
 *
 * XML representing the list of data layers, or else an error.
 * {@example docs/examples/wapi_error.txt}
 *
 * @package WebAPI
 */
/**
  * @ignore
  */
function _config_listmylayers() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_listmylayers() {
	header('Content-type: text/xml');
}
function _dispatch_listmylayers($template, $args) {
$world = $args['world'];
$user = $args['user'];
if(strtolower($_REQUEST['type'])=='bookmarked'){
	$layers = $user->getLayerBookmarks();
}else{
	$layers = $user->listLayers();
}
$template->assign('layers', $layers);

$template->display('wapi/listmylayers.tpl');
} ?>
