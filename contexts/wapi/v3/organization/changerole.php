<?php

function _config_changerole() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_changerole($template, $args) {
$user = $args['user'];
$world = $args['world'];

$org = $world->getOrganizationById($_REQUEST['id']);
if (!$org) {
   print javascriptalert('That organization was not found, or is unlisted.');
   return print redirect('organization.list');
}
if($org->owner->id != $args['user']->id and !$user->admin){
	print javascriptalert('You are not the owner of this organization.');
	return print redirect('organization.info&id='.$org->id);
}
if(isset($_REQUEST['employee']) && isset($_REQUEST['seat'])){
	$org->group->setSeat($_REQUEST['employee'], $_REQUEST['seat']);
}
}
?>