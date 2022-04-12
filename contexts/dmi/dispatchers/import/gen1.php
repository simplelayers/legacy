<?php
/**
 * The form for importing GEN data.
 * @package Dispatchers
 */
use utils\PageUtil;
/**
  */
function _config_gen1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_gen1($template, $args,$org,$pageArgs) {
$user = $args['user'];
$pageArgs['pageSubnav'] = 'data';
$pageArgs['pageTitle'] = 'Data - Import GEN';
PageUtil::SetPageArgs($pageArgs, $template);

$pageArgs = PageUtil::MixinPlanArgs($template);
if(!$pageArgs['canCreate']) {
    print javascriptalert('You are at or above your layer limit.');
    redirect('?do=layer.list');
}
// are they allowed to be doing this at all? 
/*if ($user->accounttype < AccountTypes::GOLD) {
   print javascriptalert('You must upgrade your account in order to import this format.');
   return print redirect("layerlist");
}*/

$template->display('import/gen1.tpl');

}?>
