<?php
use utils\PageUtil;
System::RequireColorPicker();
/**
 * The form for editing an entry in a layer's default color scheme.
 * @package Dispatchers
 */
/**
  */
function _config_colorschemeedit1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_colorschemeedit1($template, $args,$og,$pageArgs) {
$world = $args['world'];
$user = $args['user'];


// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}
$template->assign('layer',$layer);

// set the $nofill variable to true/false, indicating whether to suppress display of the fill field
// this is for lines, which do not have a fill
$nofill = false;
if ($layer->geomtype == GeomTypes::LINE) { $nofill = true; }
$template->assign('nofill',$nofill);

// load the entry
$entry = $layer->colorscheme->getEntryByid($_REQUEST['cid']);
if (!$entry) {
   print javascriptalert('Unable to load the specified scheme entry.');
   return print redirect("default.colorscheme&id={$layer->id}");
}
$template->assign('entry',$entry);

// send the template the options for criteria1 and criteria2

$template->assign('criteria2_list',Comparisons::GetEnum()->ToOptionAssoc());
$criteria1_list = array_keys($layer->getAttributes()); array_unshift($criteria1_list,'');
$template->assign('criteria1_list',$criteria1_list);

// the list of symbols that exist for this layer's geometry
global $SYMBOLSIZES;
$template->assign('symbolsizes', SymbolSize::GetEnum()->ToOptionAssoc());
$template->assign('symbols', $world->getMapper()->listSymbols($layer->geomtypestring)->ToOptionAssoc() );

// the color picker, a whole mess of HTML!
$template->assign('colorpicker_fill', color_picker('theform','fill_color','Fill Color',$entry->fill_color,true) );
$template->assign('colorpicker_stroke', color_picker('theform','stroke_color','Stroke Color',$entry->stroke_color,false) );
if ($nofill) $template->assign('colorpicker_fill','');

$pageArgs['pageSubnav'] = "data";
$pageArgs['pageTitle'] = 'Data - Editing classification rule for layer '.$layer->id;
$pageArgs['layerId'] = $layer->id;
PageUtil::SetPageArgs($pageArgs, $template);
// and off to the renderer
$template->display('default/colorschemeedit1.tpl');

}?>
