<?php

use utils\UserIconUtil;
function _config_icon() {
	$config = Array();
	// Start config
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _headers_icon() {
	WAPI::SetWapiHeaders(WAPI::FORMAT_PNG);

}


function _dispatch_icon($template, $args) {
	
	ob_start();
	$userid = RequestUtil::Get('id',SimpleSession::Get()->GetUser()->id);
	$size=RequestUtil::Get('size',UserIconUtil::ICON_SIZE_FULL);
	
	$file = UserIconUtil::GetIconURL($userid,$size);
	
	ob_end_clean();
	
	
	if(file_exists($file)) {
		WAPI::SetWapiHeaders(WAPI::FORMAT_PNG);
		
		die(readfile($file));
		
	}
	return false;
	
}

?>