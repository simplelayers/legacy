<?php
/**
 * The form to upload a zipfile full of kml, and have them imported into layers.
 * @package Dispatchers
 */
use utils\PageUtil;
/**
  */
function _config_kml1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_kml1($template, $args,$org,$pageArgs) {
$user = $args['user'];
/*if ($args['user']->community && count($args['user']->listLayers()) >= 3) {
	print javascriptalert('You cannot create more than 3 layers with a community account.');
	return print redirect('layer.list');
}*/

$pageArgs = PageUtil::MixinPlanArgs($template);
if(!$pageArgs['canCreate']) {
    print javascriptalert('You are at or above your layer limit.');
    redirect('?do=layer.list');
    
    
}
$pageArgs['pageSubnav'] = 'data';
if(isset($pageArgs['layerid'])) {
    $pageArgs['pageTitle'] = 'Data - Updating Layer #'.$pageArgs['layerid'].' via KML / KMZ';
} else {
    $pageArgs['pageTitle'] = 'Data - Import KML / KMZ Files';
}
PageUtil::SetPageArgs($pageArgs, $template);
// are they allowed to be doing this at all? 
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to import this format.');
   return print redirect("layer.list");
}*/

$template->assign('maxfilesize', (int) ini_get('upload_max_filesize') );
$template->display('import/kml1.tpl');

}?>
