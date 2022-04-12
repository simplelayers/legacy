<?php
use utils\ParamUtil;
/**
 * Given a layer ID, generate a thumbnail/preview image.
 * @package Dispatchers
 */
/**
 */
function _config_imagethumbnail() {
	$config = Array ();
	// Start config
	$config ["header"] = false;
	$config ["footer"] = false;
	$config ["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_imagethumbnail() {
	// eader('Content-type: image/jpeg');
}
function _dispatch_imagethumbnail($template, $args) {
	$user = SimpleSession::Get()->GetUser();
	$world = System::Get ();
	$ini = System::GetIni();
	
	$layerId = RequestUtil::Get ( 'id' );
	
	$layer = Layer::GetLayer( $layerId );
    	

            
	if (! $layer or $layer->getPermissionById ( $user->id ) < AccessLevels::READ)
		return '';
		
		// should we force generation of a new image, or use the cache?
	$cachefile = "";// "{$ini->thumbdir}/thumbnail-{$ini->name}-{$layer->id}.jpg";
	
	// list($force) = ParamUtil::GetBoolean( $_REQUEST,'force');
        $force=true;
	$layer->GenerateThumbnail($force,true,true);
        
}
// simply print the contents of the cache file
// b_end_flush();
// eturn isset($_REQUEST['filenameonly']) ? $cachefile : readfile($cachefile);

?>
