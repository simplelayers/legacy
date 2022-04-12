<?php 
ini_set('display_errors',true);
error_reporting(E_ALL);
#require_once(dirname(__FILE__).'/../../classes/StatusUpdate.php');

function _config_ico_button() {
	//$config = Array();
	//WAPI::DecorateConfig($config);
	//return $config;
}

function _headers_ico_button() {
	header( 'Content-Type: text/html; charset=utf-8',true );
	header('Content-Length: 10');
	//WAPI::SetWapiHeaders();	
}


/**
  * @ignore
  */
function _dispatch_ico_button($template, $args) {
	/* var $statusUpdate StatusUpdate */
	$statusUpdate = new StatusUpdate();
	$out = $template->fetch('devtools/ico_button.tpl');
	echo $out;
	return;
	
}

?>
