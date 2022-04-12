<?php
use utils\PageUtil;
use model\License;
use model\Permissions;
/**
  * The form for creating a new blank vector data layer.
  * @package Dispatchers
  */
/**
  */
function _config_create1() {
	$config = Array();
	// Start config
	$config["sendWorld"] = false;
	// Stop config
	return $config;
}

function _dispatch_create1($template, $args,$org,$pageArgs) {
$user = SimpleSession::Get()->GetUser();
$pageArgs['pageSubnav'] = 'data';
$pageArgs['pageTitle'] = 'Data - New Feature-layer';
PageUtil::SetPageArgs($pageArgs, $template);

if($pageArgs['reachedLayerLimit']=='true') {
        print javascriptalert('Your organization has reached its limit of '.$pageArgs['max_layers'].' layers.');
        return print redirect('layer.list');     
}

if(!Permissions::HasPerm($pageArgs['permissions'],':Layers:General:',Permissions::CREATE)) {
    print javascriptalert('You do not have permission to create new layers.');
    return print redirect('layer.list');    
}

$eGeomTypes = GeomTypes::GetVectorEnum();
$template->assign('geomtypes',$eGeomTypes->ToOptionAssoc());
$template->assign('selected',$_REQUEST['type']);

$template->display('vector/create1.tpl');

}
?>
