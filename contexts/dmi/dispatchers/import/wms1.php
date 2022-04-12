<?php
/**
 * The form for entering a new WMS layer.
 * @package Dispatchers
 */
use utils\PageUtil;
/**
  */
function _config_wms1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_wms1($template, $args,$org,$pageArgs) {
$user = $args['user'];
$pageArgs = PageUtil::MixinPlanArgs($template);
if(!$pageArgs['canCreate']) {
    print javascriptalert('You are at or above your layer limit.');
    redirect('?do=layer.list');
}
$pageArgs['pageSubnav'] = 'data';
$pageArgs['pageTitle'] = 'Data - Import WMS';
PageUtil::SetPageArgs($pageArgs, $template);

// are they allowed to be doing this at all? 
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to import this format.');
   return print redirect("layer.list");
}*/

// unlike the other import functions, WMS layers don't take up any space
// and are available to all account types, so we skip the checks

$template->display('import/wms1.tpl');

}?>
