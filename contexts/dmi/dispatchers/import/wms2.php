<?php
/**
 * Process the importwms1 form, to create a new WMS layer.
 * @package Dispatchers
 */
/**
  */
function _config_wms2() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_wms2($template, $args) {
$user = $args['user'];

// are they allowed to be doing this at all?
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to import this format.');
   return print redirect("layer.list");
}*/

// creation is easy!
$_REQUEST['name'] = $user->uniqueLayerName($_REQUEST['name']);
$layer = $user->createLayer($_REQUEST['name'],LayerTypes::WMS);
$layer->url = pruneWMSurl($_REQUEST['source']);
$reportEntry = Report::MakeEntry( REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
$report = new Report($args['world'],$reportEntry);
$report->commit();

// send them to the editing view for their new creation
return print redirect("layer.edit1&id={$layer->id}");
}?>
