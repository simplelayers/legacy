<?php

function _config_attributesalter() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_attributesalter($template, $args) {
$world = $args['world'];
$user = $args['user'];

$layer = Layer::GetLayer($_REQUEST['id']);
if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
   print javascriptalert('You do not have permission to edit that Layer.');
   return print redirect('layer.list');
 }

 $result = $layer->ChangeFieldType($_REQUEST['altattribute'],$_REQUEST['newtype']);
 if($result=="ok") {
 	$layer->owner->notify($user->id, "updated layer:", $layer->name, $layer->id, "./?do=vector.attributes&id=".$layer->id, 8);
 	print redirect("vector.attributes&id={$_REQUEST['id']}");
 } else {
	print javascriptalert($result);
 	print redirect("vector.attributes&id={$_REQUEST['id']}");
 }
 

}

?>