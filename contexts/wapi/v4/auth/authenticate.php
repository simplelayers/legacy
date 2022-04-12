<?php
use auth\Context;

use auth\Auth;
use model\SeatAssignments;
use model\Roles;
use auth\LoginMessages;
use utils\ParamUtil;
use model\Permissions;
/**
 * A list of the vector layer's columns/attributes, and widgets for adding/deleting columns.
 * 
 * @package Dispatchers
 */
/**
 */

function _exec() {
	_exec_authenticate();
}

function _exec_authenticate() {
    $ini = System::GetIni();
	WAPI::SetWapiHeaders ( WAPI::GetFormat () );
	$params = WAPI::GetParams();
	
	$permFormat = ParamUtil::Get($params,'perm_fmt','json');
	
	
	$context = Context::Get ();

	$authEnum = Auth::GetEnum ();
	
	
	$state = $authEnum [$context->authState];
	
	$format = WAPI::GetFormat ();
	
	
	$session = SimpleSession::Get();
	$userInfo = $session->GetUserInfo();
	
	$authState = $context->authState;
	$sessionState = $context->sessState;
	
	$isWAPI = is_a($context,'auth\WAPIContext') && ParamUtil::Get($params,'application')!= 'dmi';
	
	$mode = ParamUtil::Get($params,'output','verbose');
	
	$responseInfo = array();
	if($mode=='verbose') 
	   $responseInfo['state'] = $state;

	switch($authState) {
	    case Auth::STATE_ERROR_INVALID_CREDS:
	    case Auth::STATE_ERROR_NEEDPW:
	    case Auth::STATE_UNKNOWN:
	        if($isWAPI) {
	           throw new Exception($state);
	        }
	        break;
	    case AUTH::STATE_OK:
	        if($session->sessionState == SimpleSession::STATE_SESS_NONE) {
	        
	            $user = $session->GetUser();
	             $session =$session->CreateSession($user);	            
	        }
	        break;
	}
	$sessionID = $session->GetID();
	if($mode == 'verbose') {
    	$responseInfo['authState'] = $authState;
    	$responseInfo['sessionState'] = $sessionState;
	}
	if($isWAPI) $responseInfo['token'] = $sessionID;
	
	$info = array();
	LoginMessages::SetLoginMessages($authState,$sessionState, $info);
	if($mode=='verbose') $responseInfo['loginState'] =  $info;
	$userInfo = $session->GetUserInfo();
	
	if(is_null($userInfo)) $userInfo=array('username'=>'public','realname'=>'public');
	$responseInfo['username'] = $userInfo['username'];
	$responseInfo['realname'] = $userInfo['realname'];
	//$responseInfo['accounttype'] = isset($userinfo['accounttype']) ? $userInfo['accounttype'] : '';
	$seatAssignments = new SeatAssignments();
	$orgId = Organization::GetOrgByUserId($userInfo['id'],true);
	$roleId = $seatAssignments->GetUserRole($userInfo['id'],$orgId);
	$role = Roles::GetRoleById($roleId);   
	$visitor = 	    $ini = System::GetIni();;
	
	if($userInfo['username'] == $visitor && $authState == Auth::STATE_ANON) {
	    if(!ParamUtil::Get($params,'embedded')) {
	        #$responseInfo['status'] = 'Need Login';
	        throw new Exception('Need Login');
	    }
	    $responseInfo['realname']='public user';
	    //$responseInfo['realname']='public user';
	    if($mode=="verbose") {
	       $responseInfo['accounttype']='public';
	       $responseInfo['role'] = "Public" ;
	    }
	    
	    
	} else {
	    if($mode == "verbose") {
	       $responseInfo['role'] = $role['name'];
	    }
	}
	if($mode=="verbose") {
    	switch($permFormat) {
    	    case 'xml':
    	        $responseInfo['permissions']= Permissions::PermissionsToItems($session['permissions']);
    	        
    	        break;
    	    case 'none';
    	       break;
    	    default:
    	        $responseInfo['permissions'] = '_json:'.json_encode( $session['permissions']);
    	        break;
    	}
	}
	
	$session->UpdateSession();
	
	//$responseInfo['role'] =$role['name']; 
	WAPI::SendSimpleResponse($responseInfo,$format);
	return true;
	
}

?>
