<?php
/**
 * Add a Layer to a Project.
 *
 * Parameters:
 *
 * projectid -- The ID# of the Project. Note that you must own the Project.
 *
 * layerid -- The ID# of the Layer which is being added to the Project. Note that you must have read access to the layer.
 *
 * z -- The z index of the layer. -1 is on top, and they go lower to go further away from the user and toward the ground.
 *
 * on -- 1 to indicate that the ProjectLayer should be on by default when the Project is loaded.
 *
 * tooltip -- Only effective for vector layers; sets the tooltip. Optional. Example: [title]
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
function _config_addlayertoproject() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	$config["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_addlayertoproject() {
	header('Content-type: text/xml');
}
function _dispatch_wapiaddlayertoproject($template, $args) {
$world = $args['world'];
$user = $args['user'];

// fetch the project; they must own it, which makes permission checking superfluous
$project = $user->getProjectById($_REQUEST['projectid']);
if (!$project) {
   $template->assign('ok','no');
   $template->assign('message','No such Map.');
   return $template->display('wapi/okno.tpl');
}

// fetch the layer, and verify their access
$layer = $world->getLayerById($_REQUEST['layerid']);
if (!$layer) {
   $template->assign('ok','no');
   $template->assign('message','No such Layer.');
   return $template->display('wapi/okno.tpl');
}
if ($layer->getPermissionById($user->id) < AccessLevels::READ) {
   $template->assign('ok','no');
   $template->assign('message','Permission denied to use the Layer.');
   return $template->display('wapi/okno.tpl');
}

// add the Layer to the Project
$projectlayer = $project->addLayerById($layer->id);
$projectlayer->whoadded = $user->id;
$projectlayer->colorschemetype = $layer->colorschemetype;
$projectlayer->z = $_REQUEST['z'];
$projectlayer->on_by_default = $_REQUEST['on'];
if (isset($_REQUEST['tooltip'])) $projectlayer->tooltip = $_REQUEST['tooltip'];

// If it's a vector layer, copy the layer's color scheme over
if ($layer->type == LayerTypes::VECTOR) {
foreach ($layer->colorscheme->getAllEntries() as $oldcolorschemeentry) {
   $newcolorschemeentry = $projectlayer->colorscheme->addEntry();
   $newcolorschemeentry->priority     = $oldcolorschemeentry->priority;
   $newcolorschemeentry->criteria1    = $oldcolorschemeentry->criteria1;
   $newcolorschemeentry->criteria2    = $oldcolorschemeentry->criteria2;
   $newcolorschemeentry->criteria3    = $oldcolorschemeentry->criteria3;
   $newcolorschemeentry->fill_color   = $oldcolorschemeentry->fill_color;
   $newcolorschemeentry->stroke_color = $oldcolorschemeentry->stroke_color;
   $newcolorschemeentry->description  = $oldcolorschemeentry->description;
   $newcolorschemeentry->symbol       = $oldcolorschemeentry->symbol;
   $newcolorschemeentry->symbol_size  = $oldcolorschemeentry->symbol_size;
}
}

$template->assign('ok','yes');
$template->assign('message','Layer added to Map.');
$template->display('wapi/okno.tpl');

} ?>
