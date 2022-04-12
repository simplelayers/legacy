<?php
use utils\SymbolSizes;
use utils\PageUtil;
/**
 * Show the default color scheme for a layer, in a table, with links to edit those color scheme entries.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_colorscheme() {
	$config = Array ();
	// Start config
	// Stop config
	return $config;
}
function _dispatch_colorscheme($template, $args,$org,$pageArgs) {
	$world = $args ['world'];
	$user = $args ['user'];
	
	// load the layer and verify their access
	$layer = $world->getLayerById ( $_REQUEST ['id'] );
	
	if (! $layer or $layer->getPermissionById ( $user->id ) < AccessLevels::EDIT) {
		print javascriptalert ( 'You do not have permission to edit that Layer.' );
		return print redirect ( 'layer.list' );
	}
	$pageArgs['pageSubnav'] = 'data';
	$pageArgs['pageTitle'] = 'Data - Editing default classification for layer '.$layer->name;
	$pageArgs['layerId'] = $layer->id;
	PageUtil::SetPageArgs($pageArgs, $template);
	
	$template->assign ( 'layer', $layer );
	
	// set the $nofill variable to true/false, indicating whether to suppress display of the fill field
	// this is for lines, which do not have a fill
	$nofill = false;
	if ($layer->geomtype == GeomTypes::LINE) {
		$nofill = true;
	}
	$template->assign ( 'nofill', $nofill );
	
	// make a shortcut to the existing color scheme classes for use within the template
	// this also effectively sets the color scheme to Single (a simple B&W color scheme) is there isn't one yet.
	$schemeentries = $layer->colorscheme->getAllEntries ();
	
	$template->assign ( 'schemeentries', $schemeentries );
	
	// load the symbols that exist, so we can print the "pretty" name of each symbol
	$eSymbolSizes = SymbolSizes::GetEnum ();
	
	$template->assign ( 'symbols', $world->getMapper()->listSymbols( $layer->geomtypestring )->ToOptionAssoc() );
	$template->assign ( 'symbolsizes', $eSymbolSizes );
	
	// go through the attributes of this layer, and find any numeric fields. These are used to determine
	// whether to display certain color scheme links, e.g. quantile is a numeric-only thing
	function numeric_filter($a) {
		return ($a == DataTypes::FLOAT or $a == DataTypes::INTEGER);
	}
	$numericfields = array_filter ( $layer->getAttributes (), 'numeric_filter' );
	$template->assign ( 'numericfields', $numericfields );
	
	// for the labelitem: a list of columns, and the current setting
	$fields = array_keys ( $layer->getAttributes () );
	array_unshift ( $fields, '' );
	$template->assign ( 'fields', $fields );
	$template->assign ( 'labelitem', $layer->labelitem );
	// and the template, of course
	$template->display ( 'default/colorscheme.tpl' );
}
?>
