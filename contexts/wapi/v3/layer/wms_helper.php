<?php

function _config_wms_helper() {
	$config = Array();
	WAPI::DecorateConfig($config);
	return $config;
}

function _headers_wms_helper() {
	
}

function _dispatch_wms_helper($template, $args) {
	
	
	switch(RequestUtil::Get('cmd')) {
		case 'get_img':
			$url = RequestUtil::Get('url');
				
			if(stripos(strtolower($url),'getmap')<0) return;
			
			list($base,$query) = explode('?',$url);
			$query = parse_str($query);
			
			header('Content-Type: ',$query['format']);
			readfile($url);
			return;
	}
	
}
	
?>