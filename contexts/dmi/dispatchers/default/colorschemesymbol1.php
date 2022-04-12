<?php
use utils\SymbolSizes;
use utils\PageUtil;

/**
 * The form for setting the symbol for all entries in the layer's default color scheme.
 * @package Dispatchers
 */
/**
  */
function _config_colorschemesymbol1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemesymbol1($template, $args,$org,$pageArgs) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}
$template->assign('layer',$layer);


// the list of symbols that exist for this layer's geometry
$template->assign('symbols', $world->getMapper()->listSymbols($layer->geomtypestring)->ToOptionAssoc() );
$template->assign('symbolsizes', SymbolSizes::GetEnum()->ToOptionAssoc() );

$pageArgs['pageSubnav'] = 'data';
$pageArgs['layerId'] = $layer->id;
$pageArgs['pageTitle'] = 'Data - Setting the symbol for all classification rules in layer '.$layer->name;
PageUtil::SetPageArgs($pageArgs, $template);

$template->display('default/colorschemesymbol1.tpl');

}?>
