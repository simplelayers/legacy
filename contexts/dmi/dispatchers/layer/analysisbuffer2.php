<?php
/**
 * The form for creating a new layer as a spatial transform of another: buffering
 * @package Dispatchers
 */
/**
  */
  function _config_analysisbuffer2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_analysisbuffer2($template, $args) {
$world = System::Get();
$user = SimpleSession::Get()->GetUser();

// load the layer and verify their access
$layer = $world->getLayerById($_REQUEST['id']);
$permission = $layer->getPermissionById($user->id);
if (!$layer or $permission < AccessLevels::READ) {
   print javascriptalert('You do not have permission to read that layer.');
   return print redirect('layer.list');
}
if ($layer->type != LayerTypes::VECTOR and $layer->type != LayerTypes::RELATIONAL and $layer->type != LayerTypes::ODBC) {
   print javascriptalert('Not valid for this layer type.');
   return print redirect('layer.list');
}
$template->assign('layer',$layer);

// some sanity checks on the buffer, especially now that we're supposed to provide the string version of it as well
list($buffer,$buffername) = explode(' ',$_REQUEST['buffer'],2);
$buffer = floatVal($buffer);

// create a copy of the old data layer
ping("Creating copy of data layer...<br/>");
$layername = "Buffer Result".' '.date('F j, Y, g:i:s a');
$newlayer = Layer::CloneVector($layer->id);
$newlayer->name = $layername;
$newlayer->description = sprintf("Created from layer %s (%d) owned by %s\nBuffer: %s", $layer->name, $layer->id, $layer->owner->username, $buffername );
$newlayer->metadata = array('analysis'=>'buffer','source'=>$layer->id,'buffer'=>$buffername,'Description'=>$newlayer->description);

//ping("Created layer {$newlayer->id} <br/>");

$newtablename = $newlayer->url;
$oldgeomtype = $layer->geomtype;

// change the geometry to be a polygon, since buffers always are polygons
$newlayer->setDBOwnerToDatabase();
/*ping("Altering geometry type...<br/>");
$world->db->Execute("UPDATE geometry_columns SET type=? WHERE f_table_name=?", array('POLYGON',$newtablename) );
$world->db->Execute("ALTER TABLE \"{$newtablename}\" DROP CONSTRAINT \"enforce_geotype_the_geom\"");
*/

// make a single SQL call to do all of the updating
//ping("Computing buffers");

$db = System::GetDB(System::DB_ACCOUNT_SU);
$firstOld = $db->GetRow('select * from '.$layer->url.' limit 1');
unset($firstOld['the_geom']);
$fields = implode(',',array_keys($firstOld));

$query = "select $fields,ST_TRANSFORM(ST_BUFFER(ST_TRANSFORM(the_geom,3857),$buffer),4326) as the_geom into ".$newlayer->url.' from '.$layer->url;

$db->Execute($query);

$newlayer->setLayerGeomType(GeomTypes::POLYGON);
// all done; send them to their new layer's info page
return print redirect("layer.editvector1&id={$newlayer->id}");
}?>
