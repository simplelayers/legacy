<?php
use subnav\OrganizationSubnav;
use utils\ParamUtil;
use model\organizations\OrgMedia;
use auth\Context;
use model\organizations\OrgMediaImage;
function _config_media() {
	$config = Array ();
	// Start config
	if (isset ( $_REQUEST ["get"] ) || isset ( $_REQUEST ["go"] )) {
		$config ["header"] = false;
		$config ["footer"] = false;
	}
	// Stop config
	return $config;
}
function _dispatch_media($template, $args) {
	$session = SimpleSession::Get ();
	$org = ParamUtil::Get ( $session, 'orgId', ParamUtil::Get ( $_REQUEST, 'id', ParamUtil::Get ( $_REQUEST, 'orgId' ) ) );
	
	$user = $args ['user'];
	$world = $args ['world'];
	
	$presetNames = Array (
			"logo",
			"help_link" 
	);
	
	$org = $world->getOrganizationById ( $org );
	
	if (! $org) {
		print javascriptalert ( 'That organization was not found, or is unlisted.' );
		return print redirect ( 'organization.list' );
	}
	if (isset ( $_REQUEST ["go"] )) {
		$media = $org->getMedia ( $_REQUEST ["go"] );
		if ($media !== false)
			header ( 'Location: ' . $media ["link"] );
		else
			header ( 'Location: http://www.cartograph.com/guide/' );
	} else if (isset ( $_REQUEST ["get"] )) {
		
		$media = $org->getMedia ( $_REQUEST ["get"] );
		if ($media ["type"] != "plain/text") {
			$file = $org->makeLink ( $media ["name"] );
			$media ['diskusage'] = filesize ( $file );
			header ( 'Content-Type: ' . $media ["type"] );
			header ( 'Content-Length: ' . $media ["diskusage"] );
			readfile ( $file );
		} else {
			print $media ["link"];
		}
	} else {
		if (($org->owner->id != $args ['user']->id) && !Context::Get()->IsSysAdmin()) {
			print javascriptalert ( 'You are not the owner of this organization.' );
			return print redirect ( 'organization.info&id=' . $org->id );
		}
		if (isset ( $_REQUEST ["delete"] )) {
			$media = $org->deleteMedia ( $_REQUEST ["delete"] );
		}
		
		$template->assign ( 'org', $org );
		
		if (isset ( $_REQUEST ["submit"] )) {
			$ini = System::GetIni ();
			if ($_FILES ["file"] ["error"] == 0 and $_REQUEST ["type"] == "image") {
				$allowedExts = array (
						"jpg",
						"jpeg",
						"gif",
						"png" 
				);
				$allowedTypes = array (
						"image/jpeg",
						"image/pjpeg",
						"image/gif",
						"image/png" 
				);
				
				$ext = strtolower ( end ( explode ( ".", $_FILES ["file"] ["name"] ) ) );
				
				// TODO: Move max size to ini file.
				$maxSize = 5 * 1024 * 1024; // mega kilo byte
				$_REQUEST ["names"] = strtolower ( $_REQUEST ["names"] );
				
				// TODO: Move file processing to a new util class, or into Organization
				if (in_array ( $ext, $allowedExts ) && in_array ( $_FILES ["file"] ["type"], $allowedTypes ) && $_FILES ["file"] ["size"] <= $maxSize) {
					$orgId = ParamUtil::RequiresOne($_REQUEST,'orgId');
					// $dir = $ini->org_media "v2.5/org_media/" . $org->id . '/';
					$name = $_REQUEST ["names"] . "." . $ext;
					$content = ParamUtil::GetRequiredValues ( $_FILES, 'content' );
					$params = ParamUtil::GetValues ( $content, 'tmp_name:filePath', 'type:format' );
					$content = new OrgMediaImage();
					//$fileName = $org->makeFileName ( $name );
					$content->MakeOrgImage( $params );
					$media = new OrgMedia();						
					$media->UpsertOrgMedia ( $orgId, OrgMedia::MEDIATYPE_IMAGE, $content );
					//move_uploaded_file ( $_FILES ["file"] ["tmp_name"], $fileName );
					//chmod ( $dir . $name, 0755 );
					//$org->addMedia ( $_REQUEST ["names"], $name, $_FILES ["file"] ["type"], $_FILES ["file"] ['size'] );
				}
			} elseif (isset ( $_REQUEST ["text"] ) and $_REQUEST ["type"] == "text") {
				$org->addMedia ( $_REQUEST ["names"], $_REQUEST ["text"], "plain/text", 0 );
			}
		}
		$subnav = new OrganizationSubnav ();
		$subnav->makeDefault ( $user, '', $org );
		$template->assign ( 'subnav', $subnav->fetch () );
		$template->assign ( 'names', $presetNames );
		$template->display ( 'organization/media.tpl' );
	}
}
?>