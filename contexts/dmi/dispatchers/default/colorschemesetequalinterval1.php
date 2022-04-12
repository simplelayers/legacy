<?php
use utils\PageUtil;

/**
 * A form to set the layer's default color scheme to an equal-interval scheme.
 * @package Dispatchers
 */
/**
  */
function _config_colorschemesetequalinterval1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemesetequalinterval1($template, $args,$org,$pageArgs) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}
$template->assign('layer',$layer);

// go through the attributes of this layer, and find any numeric fields. These are used to determine
// whether to display certain color scheme links, e.g. quantile is a numeric-only thing
function numeric_filter($a) { return ($a==DataTypes::FLOAT or $a==DataTypes::INTEGER); }
$numericfields = array_filter($layer->getAttributes(),'numeric_filter');
$template->assign('numericfields', array_keys($numericfields) );

// how many color classes to create? here's their list of choices
$template->assign('howmany', range(2,20) );

// the selection of colorscheme is done in DHTML/JavaScript; all the server needs is the colorscheme list JSON
global $COLORSCHEMES; $template->assign('colorschemes', json_encode($COLORSCHEMES) );

$pageArgs['pageSubnav'] = 'data';
$pageArgs['pageTitle'] = 'Data - setting an <i>equal interval</i> classification for layer '.$layer->name;
$pageArgs['layerId'] = $layer->id;
PageUtil::SetPageArgs($pageArgs, $template);

$template->display('default/colorschemesetequalinterval1.tpl');

}?>
