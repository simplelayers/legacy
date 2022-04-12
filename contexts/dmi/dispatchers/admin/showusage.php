<?php
use utils\PageUtil;
/**
 * Administration: Show a list of a user's layers and corresponding disk usage.
 * 
 * @package Dispatchers
 */
/**
 */
function _config_showusage() {
	$config = Array ();
	// Start config
	$config ["admin"] = true;
	// Stop config
	return $config;
}
function _dispatch_showusage($template, $args, $org, $pageArgs) {
	
	$world = $args ['world'];
	
	// fetch the person
	$person = $world->getPersonByid ( $_REQUEST ['contactId'] );
	$pageArgs ['pageSubnav'] = 'community';
	$pageArgs['pageTitle'] = 'Admin - Person '.$person->realname."'s Usage Report";
	$pageArgs['contactName'] = $person->realname;
	if($person->id == SimpleSession::Get()->GetUser()->id) $pageArgs['pageActor'] = 'profile_owner'; 
	
	PageUtil::SetPageArgs( $pageArgs, $template );
	
	$template->assign ( 'person', $person );
	
	// fetch their list of layers, and sort it
	$layers = $person->listLayers ();
	function bysize($a, $b) {
		return ($a->diskusage < $b->diskusage) ? 1 : - 1;
	}
	usort ( $layers, 'bysize' );
	$template->assign ( 'layers', $layers );
	
	/*$subnav = new ContactSubnav ();
	$subnav->makeDefault ( $person, $args ["user"] );
	$template->assign ( 'subnav', $subnav->fetch () );
	*/
	// and draw it
	$template->display ( 'admin/showusage.tpl' );
}
?>
