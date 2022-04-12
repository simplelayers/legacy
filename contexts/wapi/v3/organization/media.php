<?php
function _config_media() {
	$config = Array ();
	// Start config
	$config ["header"] = false;
	$config ["footer"] = false;
	$config ['authUser'] = false;
	$config ['sendUser'] = false;
	$config ['sendWorld'] = true;
	
	// Stop config
	return $config;
}
function _dispatch_media($template, $args) {
	
	// user = $args['user'];
	$world = $args ['world'];
	$orgId = isset ( $_REQUEST ['id'] ) ? $_REQUEST ['id'] : 1;
	$org = $world->getOrganizationById ( $orgId );
	$org = Organization::GetOrg($orgId);
	
	if (! $org) {
		print javascriptalert ( 'That organization was not found, or is unlisted.' );
		return print redirect ( 'organization.list' );
	}
	$media = ($org->getMedia ( $_REQUEST ["get"] ) );
	
	if (isset ( $_REQUEST ["go"] )) {
		$media = $org->getMedia ( $_REQUEST ["go"] );
		if ($media === false)
			$media = $world->getOrganizationById ( 1 )->getMedia ( $_REQUEST ["go"] );
		header ( 'Location: ' . $media ["link"] );
	} elseif (isset ( $_REQUEST ["get"] )) {
		$media = $org->getMedia ( $_REQUEST ["get"] );
		
		if ($media ["type"] != "plain/text") {
			$file = $org->makeLink ( $media ["name"] );
			$fileSize = filesize ( $file );
			header ( 'Content-Type: ' . $media ["type"] );
			header ( 'Content-Length: ' . $fileSize );
			readfile ( $file );
		} else {
			print $media ["link"];
		}
	}
}

?>
