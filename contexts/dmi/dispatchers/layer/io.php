<?php
use utils\PageUtil;
function _config_io() {
	$config = Array ();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_io($template, $args, $org, $pageArgs) {
	$pageArgs ['pageSubnav'] = 'data';
	
	$world = $args ['world'];
	$user = SimpleSession::Get ()->GetUser ();
	
	$modes = array (
			'import',
			'export' 
	);
	$mode = $_REQUEST ["mode"];
	$format = $_REQUEST ["format"];
	$stage = $_REQUEST ["stage"];
	$layerId = RequestUtil::Get ( 'layerid' );
	
	
	if ($layerId)
		$pageArgs ['layerId'] = $layerId;
	
	/*
	if ($user->community && strtolower ( $_REQUEST ["format"] ) == 'wms') {
		print javascriptalert ( 'You cannot create WMS layers with a community account.' );
		return print redirect ( 'layer.list' );
	}
	
	if ($user->community && count ( $user->listLayers () ) >= 3) {
		print javascriptalert ( 'You cannot create more than 3 layers with a community account. You may overwrite an existing layer with new data.' );
	}*/
	
	if (is_null ( $stage ))
		$stage = 1;
	if (is_null ( $mode ) || is_null ( $format )) {
		print javascriptalert ( 'You must have a valid mode and format.' );
		return print redirect ( "layer.list" );
	}
	if (! in_array ( $mode, $modes )) {
		print javascriptalert ( 'You have specified an invalid mode.' );
		return print redirect ( "layer.list" );
	}
	if (! LayerFormats::HasFormat ( $format )) {
		print javascriptalert ( 'You have specified an invalid format.' );
		return print redirect ( "layer.list" );
	}
	
	$formatObj = LayerFormats::GetFormatInstance ( $format, $world, $user );
	$pageArgs['pageTitle'] = 'Data - Import '.(isset($formatObj->label) ? $formatObj->label : strtoupper($format));
	PageUtil::SetPageArgs ( $pageArgs, $template );
	/*
	 * if ($user->accounttype < $formatObj->minAccountLevel) { print javascriptalert('You must upgrade your account in order to import this format.'); return print redirect("layer.list"); }
	 */
	//$PROJECTIONS = System::RequireProjections ();
	
	// lobal $PROJECTIONS;
	switch ($mode) {
		case "import" :
			
			if ($stage == "1") {
			    if(!isset($GLOBALS['PROJECTIONS']));
			    require_once(BASEDIR.'/includes/projections.php');
			    
				$template->assign ( 'projectionlist', $GLOBALS['PROJECTIONS'] );
				
				$template->assign ( 'maxfilesize', ( int ) ini_get ( 'upload_max_filesize' ) );
				
				if (isset ( $layerId )) {
					
					// load the layer and verify their access
					$layer = $world->getLayerById ( $_REQUEST ['layerid'] );
					$permission = $layer->getPermissionById ( $user->id );
					
					if (! $layer or $permission < AccessLevels::EDIT) {
						return print redirect ( 'layer.info&id=' . $_REQUEST ['id'] );
					}
					$template->assign ( 'layer', $layer );
					
					/*
					 * $subnav = new LayerSubnav(); $subnav->makeDefault($layer, $user); $template->assign('subnav',$subnav->fetch());
					 */
					$template->display ( $formatObj->reimportTemplate );
				} else {
					// $template->assign('subnav',"");
					$template->display ( $formatObj->inputTemplate );
				}
				return;
			} elseif ($stage == 2) {
				$layerId = $formatObj->Import ($_REQUEST, $world, $user);
				//if ($layerId)
					return print redirect ( "layer.editraster1&id=$layerId" );
				
			}
			break;
		case "export" :
			/*
			$formatObj->Export ( $_REQUEST );
			*/
			break;
	}
}
?>


