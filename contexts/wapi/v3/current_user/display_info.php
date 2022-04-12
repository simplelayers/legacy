<?php

// Report all PHP errors (see changelog)
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);


/**
  * @ignore
  */

function _dispatch_display_info($template, $args) {
	
	//WAPI::SetWapiHeaders(WAPI::FORMAT_JSON);

	$user = SimpleSession::Get()->GetUser();
	
	$userInfo =array();
	$userInfo['orgid'] =  $user->getOrganization()->id;
	$userInfo['displayname'] = $user->realname;
	$userInfo['userid'] = $user->id;
	$userInfo['profile_url'] = '?do=contact.info&id='.$user->id;
	$userInfo['logout_url'] ='?do=wapi.secure.connection&action=logout';
	$userInfo['avatar_url'] = '?do->wapi.contact.icon&user='.$user->id;
	$userInfo['seat'] = $user->seatname;
	
	$results= array($user);

}


?>
