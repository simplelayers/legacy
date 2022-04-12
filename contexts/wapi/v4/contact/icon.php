<?php

	
use utils\UserIconUtil;
use utils\ParamUtil;
function _exec($template,$args) {
   
    $userId = ParamUtil::GetOne($args,'id','contactId');
    
    if(is_null($userId)) {
     $userId = SimpleSession::Get()->GetUser()->id;
    }
    $user = Person::Get($userId);
	
	if(ParamUtil::Get($args,'action')=='set_icons'){
	    UserIconUtil::SetIcons();
	    return;
	}
	
	$file = UserIconUtil::GetIconURL($user->id,ParamUtil::Get($args,'size',UserIconUtil::ICON_SIZE_FULL));
	$reset = ParamUtil::Get($args,'reset');
	if(!is_null($reset)) unlink($file);
	if(!file_exists($file)) {
	    UserIconUtil::SetIcons($userId,true);
	    $file = UserIconUtil::GetIconURL($user->id,ParamUtil::Get($args,'size',UserIconUtil::ICON_SIZE_FULL));
	}
	
	$fileDir = dirname($file);
	
	//$files =glob("$fileDir/usericon.*");
	//$file =  array_pop($files);
        $ext= explode('.',$file);
	$ext = array_pop($ext);
	
	if(file_exists($file)) {
	   switch(strtolower($ext)) {
	       case 'jpg':
	       case 'jpeg':
	           WAPI::SetWapiHeaders(WAPI::FORMAT_JPEG);
	           break;
	       case 'png':
	           WAPI::SetWapiHeaders(WAPI::FORMAT_PNG);
	           break;
	       default:
	           break;
	   }	   
	   readfile($file);
	}
	
	return false;
}

?>