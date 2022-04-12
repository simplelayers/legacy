<?php
die('here');
function _config_icon() {
	$config = Array();
	WAPI::DecorateConfig($config);
	return $config;
}

function _headers_icon() {

	
	
}
	
	
use utils\UserIconUtil;
use utils\ParamUtil;
function _dispatch_icon($template, $args) {
	$user = SimpleSession::Get()->GetUserInfo();
	
	
	$file = UserIconUtil::GetIconURL(ParamUtil::Get($args,'id',$user['id']),ParamUtil::Get($args,'size',UserIconUtil::ICON_SIZE_FULL));
	#ob_end_clean();
	if(file_exists($file)) {
		WAPI::SetWapiHeaders(WAPI::FORMAT_PNG);
		die(readfile($file));
	
	}
	return false;
	
}

?>