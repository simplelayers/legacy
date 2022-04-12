<?php
/**
  * The handler for layercreatevector1, to actually create the new layer.
  * @package Dispatchers
  */
/**
  */
function _config_create2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_create2($template, $args) {
$user = $args['user'];
$world = $args['world'];

// are they allowed to be doing this at all? 
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to create blank layers.');
   return print redirect('project.list');
}*/

// if they tried to trick us with a bad geometry type, trick them by defaulting to point
$geomTypeId = intval($_REQUEST['type']);
if($geomTypeId == 0) $geomTypeId = GeomTypes::POINT;


// create the new layer, then the DB table for its storage
$_REQUEST['name'] = $user->uniqueLayerName($_REQUEST['name']);
$layer = $user->createLayer($_REQUEST['name'],LayerTypes::VECTOR);
$layer->geom_type = $geomTypeId;
$world->db->Execute("CREATE TABLE {$layer->url} (gid serial,name text)");
$world->db->Execute("SELECT AddGeometryColumn('','{$layer->url}','the_geom',4326,'GEOMETRY',2)");
$world->db->Execute("CREATE INDEX {$layer->url}_index_the_geom ON $layer->url USING GIST (the_geom)");
$world->db->Execute("CREATE INDEX {$layer->url}_index_oid ON $layer->url (oid)");
$layer->setDBOwnerToOwner();

$reportEntry = Report::MakeEntry(REPORT_ACTIVITY_CREATE, REPORT_ENVIRONMENT_DMI, REPORT_TARGET_LAYER, $layer->id, $layer->name, $args['user']);
// done, send them to the editing view for their new layer
$report = new Report($args['world'],$reportEntry );
$report->commit();
return print redirect("layer.edit1&id={$layer->id}");
}?>
