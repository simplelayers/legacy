<?php
/**
 * A list of the vector layer's columns/attributes, and widgets for adding/deleting columns.
 * @package Dispatchers
 */
/**
 */
function _config_validate() {
	$config = Array ();
	WAPI::DecorateConfig($config);
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

	/*
	
	
	$action = RequestUtil::Get("action",'login');
	if($action=='login') {
		if(RequestUtil::HasParam('token')) RequestUtil::UnsetItems('token');
	}
	
	$context = Context::Get(Creds::GetFromRequest());
	$session = SimpleSession::Get();
	
	
	
	$authState = $context->authState;
	$sessionState = $context->sessState;
	$sessionID = $session->GetID();
	
	$isWAPI = is_a($context,'auth\WAPIContext');
	
	$response = array();
	$response['authState'] = $authState;
	$response['sessionState'] = $sessionState;
	if($isWAPI) $response['token'] = $sessionID;

	
	$format = RequestUtil::Get('format','xml');
	
	if($format=='json') die( json_encode($response) );
	
	
	
	
	$app = $context->GetApp();
	$userInfo = $session->GetUserInfo();
	if(is_null($userInfo)) $userInfo=array('username'=>'public','realname'=>'public');
	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : "login";
	$template->assign('token',$sessionID);
	$template->assign('username',$userInfo['username']);
	$template->assign('realname',$userInfo['realname']);
	//if(isset($userinfo['accounttype']))	$template->assign('accountType',$userInfo['accounttype']);
	$template->display('wapi/loggedin.tpl');
	
	
	die();
	//Deduce Context
	$username = isset ( $_REQUEST ['username'] ) ? $_REQUEST ['username'] : null;
	$password = isset ( $_REQUEST ['password'] ) ? $_REQUEST ['password'] : null;
	$hasCredentials = (!is_null($username) and !is_null($password));
	
	
	$embedded = isset ( $_REQUEST ['embedded'] ) ? ($_REQUEST ['embedded'] == "1") : false;
	$token = isset ( $_REQUEST ['token'] ) ? $_REQUEST ['token'] : null;
	
	#$connection = new SConnection( $world, $embedded );
	
	
	
	
	#$hasToken = $connection->tokenValid;
	
	if( $action == "login") {
		
		if ($hasCredentials) {
			$force = isset ( $_REQUEST ['force'] ) ? ($_REQUEST ['force'] == "1") : null;
			$connection->LoginUserPass ( $username, $password, $app, $force );
			
			//var_duump($connection->)
			if (!$connection->userValid || is_null($connection->user)) {
				if( $app == "dmi") {
					$url = (isset ( $_REQUEST ['on_loggedin'] ) ? $_REQUEST ['on_loggedin'] : "./?do=account.login");
					$url .= "&username=$username";
					header ( "Location: $url" );
				}
			} else {
				if( $app == "dmi") {
					$url = ((isset($_REQUEST ['on_ok']) && $_REQUEST ['on_ok']) ? $_REQUEST ['on_ok'] : "./?do=project.list");
					header ( "Location: $url" );
					$world->logUserLogin($connection->user->username, $_SERVER["REMOTE_ADDR"]);
				}
			}
			
			if($wapi->format == WAPI::FORMAT_JSON) {
			
				$json = array();
				$json['connection'] = array();
				$json['connection']['token'] = $world->auth->token;
				$json['connection']['user'] = array();
				$json['connection']['user']['username'] = $connection->user->username;
				$json['connection']['user']['realname'] = $connection->user->firstname.' '.$connection->user->lastname;
				//$json['connection']['user']['account'] = $connection->user->accounttype;
				die(json_encode($json));
			} else {	
				$template->assign('token',$connection->token);
				$template->assign('user',$connection->user);
				//$template->assign('accountType',$connection->user->accounttype);
				$template->display('wapi/loggedin.tpl');
				return;
			}
		} elseif( $hasToken) {
			$useSession = ($app == "dmi");
			$connection->LoginToken($token,$useSession);
			if (! $connection->userValid) {
				die ("token invalid");
			}
		} else {
			
			$connection->ConnectToSession($app);
			if( $connection->userValid ){
				if($wapi->format == WAPI::FORMAT_JSON) {
					$json = array();
					$json['connection'] = array();
					$json['connection']['token'] = $world->auth->token;
					$json['connection']['user'] = array();
					$json['connection']['user']['username'] = $connection->user->username;
					$json['connection']['user']['realname'] = $connection->user->firstname.' '.$connection->user->lastname;
					//$json['connection']['user']['account'] = $connection->user->accounttype;
					die(json_encode($json));
				} else {	
					$template->assign('token',$connection->token);
					$template->assign('user',$connection->user);
					//$template->assign('accountType',$connection->user->accounttype);
					$template->display('wapi/loggedin.tpl');
					return;
				}
			} else {
				die("no token for session");
			}
			
		}
	} else { // aciton is logout
		
		$connection->ConnectToSession($app);
		$connection->Logout($app);
		header ( "Location: ./?do=account.login" );
	}
	*/
}



	
?>
