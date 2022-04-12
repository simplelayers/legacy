<?php
use model\Permissions;
/**
 * The form for creating a new layer as a spatial transform of another: intersectioning
 * @package Dispatchers
 */
/**
  */
function _config_analysisintersection2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_analysisintersection2($template, $args,$org,$pageArgs) {
$world = $args['world'];
$user = $args['user'];

// load the first layer and verify their access
// this first layer will be copied, and will act as the starting dataset
$layer1 = $world->getLayerById($_REQUEST['layer1id']);

if(!Permissions::HasPerm($pageArgs['permissions'], ':Layers:Analysis:Intersection:',Permissions::CREATE)) {
    print javascriptalert('You do not have permission to perform this action.');
    return print redirect('layer.list');
}

if(!Permissions::HasPerm($pageArgs['permissions'], ':Layers:General:',Permissions::CREATE)) {
    print javascriptalert('You do not have permission to perform this action.');
    return print redirect('layer.list');
}

$permission = $layer1->getPermissionById($user->id);
if (!$layer1 or $permission < AccessLevels::READ) {
   print javascriptalert('You do not have permission to read that layer.');
   die();//return print redirect('layer.list');
}

// load the second layer and verify their access
// this second layer acts as a mask; features will be deleted from the copied layer which do not match this mask
$layer2 = $world->getLayerById($_REQUEST['layer2id']);
$permission = $layer2->getPermissionById($user->id);
if (!$layer2 or $permission < AccessLevels::READ) {
   print javascriptalert('You do not have permission to read that layer.');
   return print redirect('layer.list');
}

// create a copy of the starting layer
ping("Creating copy of source data layer...<br/>");


$layername = "Intersection Result".' '.date('F j, Y, g:i:s a');
$newlayer = Layer::CloneVector($layer1->id);// $user->createCopyOfLayer($layer1->id,$layername);
$newlayer->description = sprintf("Created from layer %s (%d) owned by %s\nIntersection with: layer %s (%d) owned by %s",
                                  $layer1->name, $layer1->id, $layer1->owner->username,
                                  $layer2->name, $layer2->id, $layer2->owner->username
			  );
$newlayer->metadata = array('analysis'=>'intersection','source'=>$layer1->id,'mask'=>$layer2->id,'Description'=>$newlayer->description);
$newlayer->name = $layername; 
//ping("Finding intersecting records<br/>");
$matching_gids = array();

$db = System::GetDB(System::DB_ACCOUNT_SU);
//$db->Execute('select * into '.$newlayer->url.' from '.$layer1->url);

//$maskgeom = $world->db->Execute("SELECT ST_Union(the_geom) as maskgeom from {$layer2->url}")->fields['maskgeom'];



// delete from the new layer where the gid isn't in the list of ones that do match the mask
$newlayer->setDBOwnerToDatabase();
ping("Purging features not fitting the mask...<br/>");

switch ($_REQUEST['operationType']) {
    case 2: //Non-intersection
        $query = "Select * into {$newlayer->url} from {$layer1->url} where gid not in (select t1.gid from {$layer1->url} as t1, {$layer2->url} as t2 WHERE st_Intersects(t1.the_geom,t2.the_geom)  AND t1.the_geom IS NOT NULL AND t2.the_geom IS NOT NULL)";
        //$world->db->Execute("DELETE FROM {$newlayer->url} where gid in ( select t1.gid from {$newlayer->url} as t1, {$layer2->url} as t2 WHERE st_Intersects(t1.the_geom,t2.the_geom)  AND t1.the_geom IS NOT NULL )");
        $db->Execute($query);
        break;
    default: //Intersection
        $query = "select t1.* into {$newlayer->url} from {$layer1->url} as t1, {$layer2->url} as t2 WHERE st_Intersects(t1.the_geom,t2.the_geom)  AND t1.the_geom IS NOT NULL and t2.the_geom IS NOT NULL ";
        $db->Execute($query);
        //$world->db->Execute("DELETE FROM {$newlayer->url} where not gid in ( select t1.gid from {$newlayer->url} as t1, {$layer2->url} as t2 WHERE  st_Intersects(t1.the_geom,t2.the_geom)  AND t1.the_geom IS NOT NULL)");
        //$world->db->Execute("DELETE FROM \"{$newlayer->url}\" WHERE NOT st_Intersects(the_geom,'$maskgeom') AND NOT the_geom IS NULL");
        break;
}

// vacuum and re-analyze the target layer, since many records have been deleted
ping("Compacting table space...<br/>");
$world->db->Execute("VACUUM FULL \"{$newlayer->url}\"");
ping("Revising spatial index...<br/>");
$world->db->Execute("ANALYZE \"{$newlayer->url}\"");
$newlayer->setDBOwnerToOwner();

// all done; send them to their new layer's info page
return print redirect("layer.editvector1&id={$newlayer->id}");

}?>
