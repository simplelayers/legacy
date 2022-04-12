<?php
use utils\PageUtil;

/**
 * The form to set the layer's default color scheme to a quantile scheme.
 * @package Dispatchers
 */
/**
  */
function _config_colorschemesetquantile1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemesetquantile1($template, $args,$org,$pageArgs) {
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
$pageArgs['layerId'] = $layer->id;
$pageArgs['pageTitle'] = 'Data - Setting <i>quantile distribution</i> classification for '.$layer->name;
PageUtil::SetPageArgs($pageArgs, $template);

// all set for HTML
$template->display('default/colorschemesetquantile1.tpl');

}?>
