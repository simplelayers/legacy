<?php
use utils\PageUtil;
/**
 * The form to upload a zipfile full of shapefiles, and have them imported into layers.
 * @package Dispatchers
 */
/**
  */
function _config_shapefiles1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_shapefiles1($template, $args) {
$user = $args['user'];

$pageArgs = PageUtil::MixinPlanArgs($template);
if(!$pageArgs['canCreate']) {
    print javascriptalert('You are at or above your layer limit.');
    redirect('?do=layer.list');
}
// are they allowed to be doing this at all? 
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to import this format.');
   return print redirect("layer.list");
}*/

global $PROJECTIONS;
$template->assign('projectionlist',$PROJECTIONS);
$template->assign('maxfilesize', (int) ini_get('upload_max_filesize') );
$template->display('import/shapefiles1.tpl');

}?>
