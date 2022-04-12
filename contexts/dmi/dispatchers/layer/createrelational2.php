<?php
/**
  * The handler for layercreaterelational2, to actually create the new layer.
  * @package Dispatchers
  */
/**
  */
function _config_createrelational2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_createrelational2($template, $args) {
$user = $args['user'];

$world = $args['world'];

// are they allowed to be doing this at all?
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to create relational layers.');
   return print redirect('project.list');
}*/

// create the new layer
$_REQUEST['name'] = $user->uniqueLayerName($_REQUEST['name']);
$layer = $user->createLayer($_REQUEST['name'],LayerTypes::RELATIONAL);

// give it a description based on the columns, tables, etc.
$layer1 = $world->getLayerById($_POST['table1']);
$layer2 = $world->getLayerById($_POST['table2']);
$layer->description = sprintf("Relational table.\nSpatial info in layer %s, using column %s for relation.\nSupplemental info in layer %s, using column %s for relation.\nRelation type is %s JOIN.",
    $layer1->name, $_POST['column1'],
    $layer2->name, $_POST['column2'],
    strtoupper($_POST['relationtype'])
);

// insert the stub geometry type record. this will be overwritten in a moment
//$world->db->Execute("INSERT INTO geometry_columns (f_table_catalog,f_table_schema,f_table_name,f_geometry_column,coord_dimension,srid,type) VALUES (?,?,?,?,?,?,?)", array('','public',$layer->url,'the_geom',2,4326,'POINT') );

// call the relations-saver dispatcher, rather than duplicate all that hugely complex code
require_once('relations2.php');
$_REQUEST['id'] = $layer->id;
$sys = System::Get();
$entry = Report::MakeEntry( REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $user);
$report = new Report($sys,$entry);
$report->commit();
_dispatch_relations2($template, $args);

}?>
