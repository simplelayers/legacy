<?php
use utils\PageUtil;
System::RequireColorPicker();
/**
 * A form to set the layer's default color scheme to a single-entry scheme.
 * @package Dispatchers
 */
/**
  */
function _config_colorschemesetsingle1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemesetsingle1($template, $args,$org,$pageArgs) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}
$template->assign('layer',$layer);

$template->assign('colorpicker1', color_picker('theform','fillcolor','','#000000','',false) );
$pageArgs['pageSubnav']= 'data';
$pageArgs['layerId'] = $layer->id;
PageUtil::SetPageArgs($pageArgs, $template);

$template->display('default/colorschemesetsingle1.tpl');

}?>
