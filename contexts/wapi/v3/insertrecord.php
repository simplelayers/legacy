<?php
/**
 * Insert a record into a Layer which you own.
 *
 * Parameters:
 *
 * layer -- The layer-ID into which the feature is being inserted, e.g. 1234.
 *          Note that you must own the Layer.
 *
 * wkt_geom -- A well-known text geometry (WKT) appropriate for use in the layer.
 *             Coordinates must be in NAD83/WGS84.
 *             Example: POINT(-120.001 39.455)
 *
 * all other params -- will be treated as data columns and inserted as given
 *
 * Return:
 *
 * XML representing the status of the request.
 * In the case of OK, the 'ok' value will be the numeric gid of the new feature, not simply 'yes'
 * {@example docs/examples/wapi_ok.txt}
 * {@example docs/examples/wapi_no.txt}
 *
 * @package WebAPI
 */
/**
  * @ignore
  */
function _config_insertrecord() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_insertrecord() {
	header('Content-type: text/xml');
}
function _dispatch_insertrecord($template, $args) {
$world = $args['world'];
$user = $args['user'];

// fetch the layer; they must own it, which makes permission checking superfluous
$layer = $user->getLayerById($_REQUEST['layer']);
if (!$layer) {
   $template->assign('ok','no');
   $template->assign('message','No such layer.');
   return $template->display('wapi/okno.tpl');
}

$record = $layer->insertRecord($_REQUEST);

$template->assign('ok',$record['gid']);
$template->assign('message',"New record inserted as gid {$record['gid']}");
return $template->display('wapi/okno.tpl');
} ?>
