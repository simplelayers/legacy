<?php
use utils\ParamUtil;
/**
 * Delete one of your own layers.
 *
 * Parameters:
 *
 * id -- The layer-ID to delete, e.g. 1234. Note that the user calling must own the layer.
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
function _config_deletelayer() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_deletelayer() {
	header('Content-type: text/xml');
}
function _dispatch_wapideletelayer($template, $args) {
$world = $args['world'];
$user = $args['user'];

// fetch the layer; they must own it, which makes permission checking superfluous
$layer = $user->getLayerById(ParamUtil::Get(WAPI::GetParams(),'layerId'));
if (!$layer) {
   $template->assign('ok','no');
   $template->assign('message','No such layer.');
   return $template->display('wapi/okno.tpl');
}

$layer->delete();
$template->assign('ok','yes');
$template->assign('message','Layer deleted.');
$template->display('wapi/okno.tpl');

} ?>
