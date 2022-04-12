<?php
use utils\PageUtil;

/**
 * The form for creating a new layer as a spatial transform of another: buffering
 * @package Dispatchers
 */
/**
  */
function _config_analysisbuffer1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_analysisbuffer1($template, $args,$org,$pageArgs) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
$permission = $layer->getPermissionById($user->id);
if (!$layer or $permission < AccessLevels::READ) {
   print javascriptalert('You do not have permission to read that layer.');
   return print redirect('layer.list');
}
if ($layer->type != LayerTypes::VECTOR and $layer->type != LayerTypes::RELATIONAL and $layer->type != LayerTypes::ODBC) {
   print javascriptalert('Not valid for this layer type.');
   return print redirect('layer.list');
}
if ($user->community && count($user->listLayers()) >= 3) {
	print javascriptalert('You cannot create more than 3 layers with a community account.');
	return print redirect('layer.list');
}
$template->assign('layer',$layer);

$pageArgs['pageSubnav'] = 'data';
$pageArgs['layerId'] = $layer->id;
$pageArgs['pageTitle'] = 'Data - Buffering layer '.$layer->name;
PageUtil::SetPageArgs($pageArgs, $template);

// and the template, as always
$template->display('layer/analysisbuffer1.tpl');

}?>
