<?php
use utils\PageUtil;
System::RequireColorPicker();
/**
 * The form for setting the layer's default color scheme to a unique-value scheme.
 * @package Dispatchers
 */
/**
  */
function _config_colorschemesetunique1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemesetunique1($template, $args,$org,$pageArgs) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}
$template->assign('layer',$layer);

// the list of column choices
$template->assign('fields', array_keys($layer->getAttributes()) );

// the selection of colorscheme is done in DHTML/JavaScript; all the server needs is the colorscheme list JSON
global $COLORSCHEMES; $template->assign('colorschemes', json_encode($COLORSCHEMES) );

$pageArgs['pageSubnav'] = 'data';
$pageArgs['layerId'] = $layer->id;
$pageArgs['pageTitle'] = 'Data - Setting <i>unique value</i> classification for '.$layer->name;
PageUtil::SetPageArgs($pageArgs, $template);
// all set
$template->display('default/colorschemesetunique1.tpl');

}?>
