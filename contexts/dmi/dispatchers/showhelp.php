<?php

function _config_showhelp() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_showhelp($template, $args) {
$user = $args['user'];
$world = $args['world'];
$org = $world->getOrganizationById($_REQUEST['org']);
if (!$org) {
   print javascriptalert('That organization was not found, or you are not a member.');
   return print redirect('mainmenu');
}
$media = $org->getMedia("help_link");
if($media["link"] == "") header("Location: http://www.cartograph.com/guide/");
else header("Location: ".$media["link"]);
}?>