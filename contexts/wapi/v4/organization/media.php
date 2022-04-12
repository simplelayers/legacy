<?php
use utils\ParamUtil;
use model\organizations\OrgMedia;

function _exec($template,$args) {
	
    
  	$user = SimpleSession::Get()->GetUser();
	$world = System::Get();
	$orgId = ParamUtil::Get($_REQUEST,'id',ParamUtil::Get($_REQUEST,'orgId'));
	$org = null;
	if(is_null($orgId)) {
		$org = Organization::GetOrgByUserId($user->id,false);
		
	} else {
		$org = Organization::GetOrg($orgId);
	}
	
	$orgMedia = new OrgMedia();
	
	if (!$org) {
		print javascriptalert('That organization was not found, or is unlisted.');
		return print redirect('organization.list');
	}
	$_REQUEST['orgId'] = $org->id;
	
	
	
	if( isset($_REQUEST["go"]) ) {
		$orgMedia = new OrgMedia();
		if(!$orgMedia->Go($_REQUEST)) {
		    throw new Exception('Requested media not available');
		} 
		    
		
	} elseif( isset($_REQUEST["get"]) ) {
		$media = $org->getMedia($_REQUEST["get"]);
		
		if( $media["type"] != "plain/text" ){
			$file = $org->makeLink($media["name"]);
			$fileSize  = filesize($file);
			header('Content-Type: ' . $media["type"]);
			header('Content-Length: ' . $fileSize);
			readfile($file);
		} else {
			print $media["link"];
		}
	}
	
}