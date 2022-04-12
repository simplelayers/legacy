<?php
use auth\Context;
use auth\Creds;
use enums\AccountTypes;
/**
 * A list of the vector layer's columns/attributes, and widgets for adding/deleting columns.
 * @package Dispatchers
 */
/**
 */
function _config_secure_connection() {
	$config = Array ();
	// Start config
	$config ["header"] = false;
	$config ["footer"] = false;
	$config ['authUser'] = 0;
	$config ['sendUser'] = false;
	$config ["customHeaders"] = true;
	// Stop config
	return $config;
}
function _headers_secure_connection() {
	$format = RequestUtil::Get('format','xml');
	
	switch($format) {
		case "json":	
		case"ajax":
			header('Content-Type: application/json');
			break;
		case "xml":
			header('Content-Type: text/xml');
			break;
	}	
	#header( 'Content-Type: text/html');
}

function _dispatch_secure_connection($template, $args) {
    
	$action = RequestUtil::Get("action",'login');
	if($action=='login') {
		if(RequestUtil::HasParam('token')) RequestUtil::UnsetItems('token');
	}
	$params = WAPI::GetParams();
	$context = Context::Get(Creds::GetFromRequest());
	$session = SimpleSession::Get();
		
	
	$authState = $context->authState;
	$sessionState = $context->sessState;
	

	
	
	$isWAPI = is_a($context,'auth\WAPIContext');
	
	$response = array();
	$response['authState'] = $authState;
	$response['sessionState'] = $sessionState;
	//if($isWAPI) $response['token'] = $sessionID;

	$template->assign('token',$args['token']);
	$format = RequestUtil::Get('format','xml');
	
	if($format=='json') die( json_encode($response) );
	
	
	
	
	$app = $context->GetApp();
	$userInfo = $session->GetUserInfo();
	
	if(is_null($userInfo)) $userInfo=array('username'=>'public','realname'=>'public');
	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : "login";
	
	$template->assign('username',$userInfo['username']);
	$template->assign('realname',$userInfo['realname']);
	//if(isset($userinfo['accounttype']))	$template->assign('accountType',$userInfo['accounttype']);
	$template->display('wapi/loggedin.tpl');
	
	
	die();

}



	
?>
