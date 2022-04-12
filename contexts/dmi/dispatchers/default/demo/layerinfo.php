<?php
/**
 * The "Search Community" subsystem -- show info about a layer.
 * @package Dispatchers
 */
/**
  */
function _config_layerinfo() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["authUser"] = 0;
	// Stop config
	return $config;
}

function _dispatch_layerinfo($template, $args) {
$world = $args['world'];

$layer = $world->getLayerById($_REQUEST['id']);
if (!$layer or !$layer->getPermissionById(false)) {
   print javascriptalert('That layer was not found, or is unlisted.');
   return print redirect('demo.search');
}

$template->assign('taglinks', activate_tags($layer->tags,'.?do=demo.searchlayers&search=') );
$template->assign('layer',$layer);
$template->display('demo/layerinfo.tpl');

}?>
