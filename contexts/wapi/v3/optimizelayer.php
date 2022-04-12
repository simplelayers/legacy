<?php
/**
 * Optimize one of your own layers, by running VACUUM and ANALYZE.
 * This is mainly useful after a layer has been created, or numerous records optimize/gd or added.
 *
 * Parameters:
 *
 * id -- The layer-ID to optimize/g, e.g. 1234. Note that the user calling must own the layer.
 *
 * Return:
 *
 * XML representing the status of the request.
 * {@example docs/examples/wapi_ok.txt}
 * {@example docs/examples/wapi_no.txt}
 *
 * @package WebAPI
 */
/**
  * @ignore
  */
function _config_optimizelayer() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_optimizelayer() {
	header('Content-type: text/xml');
}
function _dispatch_optimizelayer($template, $args) {
$world = $args['world'];
$user = $args['user'];

// fetch the layer; they must own it, which makes permission checking superfluous
$layer = $user->getLayerById($_REQUEST['id']);
if (!$layer) {
   $template->assign('ok','no');
   $template->assign('message','No such layer.');
   return $template->display('wapi/okno.tpl');
}
if ($layer->type != LayerTypes::VECTOR) {
   $template->assign('ok','no');
   $template->assign('message','This operation is only usable for vector data layers.');
   return $template->display('wapi/okno.tpl');
}

$layer->optimize();
$template->assign('ok','yes');
$template->assign('message','Layer optimized.');
$template->display('wapi/okno.tpl');

} ?>
