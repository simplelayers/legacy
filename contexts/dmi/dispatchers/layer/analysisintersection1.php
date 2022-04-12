<?php
use utils\PageUtil;

/**
 * The form for creating a new layer as a spatial transform of another: intersectioning
 * @package Dispatchers
 */
/**
  */
function _config_analysisintersection1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_analysisintersection1($template, $args,$org,$pageArgs) {
$world = $args['world'];
$user = $args['user'];
// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
$permission = $layer->getPermissionById($user->id);
if (!$layer or $permission < AccessLevels::READ) {
   print javascriptalert('You do not have permission to read that layer.');
   return print redirect('layer.list');
}
if ($user->community && count($user->listLayers()) >= 3) {
	print javascriptalert('You cannot create more than 3 layers with a community account.');
	return print redirect('layer.list');
}
$template->assign('layer',$layer);

// with which other layers can it possibly intersect?
$layers = array();
foreach (array_merge( $user->listLayers() , $user->getLayerBookmarks() ) as $l) {
   if($l->geomtypestring != "collection") $layers[$l->id] = sprintf("%s :: %s (%s)", $l->owner->username, $l->name, $l->geomtypestring );
}
asort($layers);
$template->assign('layers',$layers);

$pageArgs['pageSubnav'] = 'data';
$pageArgs['layerId'] = $layer->id;
$pageArgs['pageTitle'] = 'Data - Intersecting layer '.$layer->name;
PageUtil::SetPageArgs($pageArgs, $template);

// and the template, as always
$template->display('layer/analysisintersection1.tpl');

}?>
