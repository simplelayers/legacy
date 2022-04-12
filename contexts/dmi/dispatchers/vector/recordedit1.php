<?php
use utils\PageUtil;
use model\Permissions;

/**
 * Form to edit a record in the specified vector layer; called from vectorrecords.
 * @package Dispatchers
 */
/**
  */
function _config_recordedit1() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_recordedit1($template, $args,$org,$pageArgs) {
$world = $args['world'];
$user = $args['user'];

// load the layer and verify their access
$layer = Layer::GetLayer($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::READ) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
}
$template->assign('layer',$layer);


$pageArgs['pageSubnav'] = 'data';
$pageArgs['layerId'] = $layer->id;
PageUtil::SetPageArgs($pageArgs, $template);
$pageArgs = PageUtil::MixinLayerArgs($template);

$isEditor = (($pageArgs['isLayerEditor']=='true') && ($pageArgs['hasEditableRecords']=='true'));


// load the record, ensure that it exists
$includeWKT = isset($_REQUEST['inc_wkt']);
if(!$isEditor) $includeGeom=false;
$template->assign('includeWKT',$includeWKT);
$withGeomURL = BASEURL."?do=vector.recordedit1&id={$layer->id}&gid={$_REQUEST['gid']}&inc_wkt=1";
$withoutGeomURL = BASEURL."?do=vector.recordedit1&id={$layer->id}&gid={$_REQUEST['gid']}";
$template->assign('withGeomURL',$withGeomURL);
$template->assign('withoutGeomURL',$withoutGeomURL);


$db = System::GetDB(System::DB_ACCOUNT_SU);

$record = $layer->getRecordById($_REQUEST['gid'],true,!$includeWKT);

if (!$record) {
   print javascriptalert('That record does not exist.');
   
   return print redirect("vector.records&id={$layer->id}");
}

//die();
$pageArgs['pageTitle'] = ($isEditor) ? 'Editing record #'.$record['gid'].' for layer '.$layer->name : 'Viewing record '.$record['id'].' for layer '.$layer->name;
$pageArgs['featureId'] = $record['id'];


$template->assign('isRecordEditor',$isEditor);
PageUtil::SetPageArgs($pageArgs, $template);
// we don't want the gid or the the_geom as part of the output
// the gid is immutable, and the the_geom is represented by the wkt_geom field
$template->assign('gid',$record['gid']);

unset($record['the_geom']);
unset($record['gid']);
unset($record['id']);
unset($record['field_info']);
$template->assign('record',$record);
$template->assign('hasGeom',GeomTypes::IsVector($layer->geom_type));
// load the datatypes for this layer
$columns = $layer->getAttributesVerbose(true, true);
$columns['wkt_geom'] = Array('requires'=>'geometry', 'display'=>'wkt_geom');
//$columns['box_geom'] = Array('requires'=>'geometry', 'display'=>'box_geom');
$template->assign('columns',$columns);



/*$subnav = new LayerSubnav();
$subnav->makeDefault($layer, $user);
$template->assign('subnav',$subnav->fetch());*/

// okie doke, off to the template
$template->display('vector/recordedit1.tpl');

}?>
