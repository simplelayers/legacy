<?php

namespace utils;
use \System;
use auth\Context;

System::RequireSimpleImage();

class UserIconUtil {
	
	const SIZE_FULL = 200;
	const SIZE_SM = 50;
	
	const ICON_SIZE_SM = 'small';
	const ICON_SIZE_FULL = '';
	
	public static function GetIconURL($userId,$size='') {
		$ini = \System::GetIni();
		$size = ($size==self::ICON_SIZE_FULL) ? '': '_sm';

		$files = glob(sprintf("%s%d/usericon.*",$ini->usermedia,$userId,$size));
		$file = array_pop($files);
		if(!file_exists($file)) {
		    self::SetIcons($userId);
		}
		$files = glob(sprintf("%s%d/usericon_sm.*",$ini->usermedia,$userId,$size));
		$file_sm = array_pop($files);
		if(!file_exists($file_sm)) {
		    self::SetIcons($userId);
		}
		if($size=='_sm') return $file_sm;
		return $file; 
		
		//return sprintf("%s%d/usericon%s.png",$ini->usermedia,$userId,$size);							
	}
	
	public static function SetIcons($userId=null,$reset=false,$fileName=null) {
		
		$db = System::GetDB(System::DB_ACCOUNT_SU);
		$ini = System::GetIni();
		$people = !is_null($userId) ? array(System::Get()->getPersonById($userId)) : $db->Execute( 'select id,email,realname from people where email is not null and email_public=true'); 
		
		foreach($people as $user) {
		    
		    $userId = is_a($user,'Person') ? $user->id : $user['id'];
		    
			$simpleImg = new \SimpleImage();
			$basePath = sprintf("%s%d",$ini->usermedia,(int)$userId);
			if(!file_exists($basePath)) {
			    mkdir($basePath);
			    exec('chmod '.$basePath.' ugo+x');
			}
			
			$fileName =is_null($fileName) ? "usericon.tmp.jpg" : $fileName; 
			
			$full =  sprintf("%s%d/%s",$ini->usermedia,(int)$userId,$fileName);
			$sm = sprintf("%s%d/usericon.jpg",$ini->usermedia,(int)$userId);
			$full_png = sprintf("%s%d/usericon.png",$ini->usermedia,(int)$userId);
			$sm_png = sprintf("%s%d/usericon_sm.png",$ini->usermedia,(int)$userId);
			if(!file_exists($full_png)) $reset = true;
			if(file_exists($full_png)) {
			    if(filesize($full_png)==0) $reset = true; 
			}
			if($reset && !file_exists($full)) {
			    self::UpdateIcons($user);
			    $reset=false;
			}
			
			if(file_exists($full) || $reset) {
			    
			    if(file_exists($sm_png)) unlink($sm_png);
			    if(file_exists($full_png)) unlink($full_png);

				$simpleImg->load($full);
				
				$simpleImg->resize(self::SIZE_FULL,self::SIZE_FULL);
				$simpleImg->save($full_png,IMAGETYPE_PNG);
				
				$simpleImg->load($full);
				$simpleImg->resize(self::SIZE_SM,self::SIZE_SM);
				$simpleImg->save($sm_png,IMAGETYPE_PNG);
				if(file_exists($full)) unlink($full);
				continue;							
			} 			
			
			
		}
	}
	
	public static function UpdateIcons($userInfo) {
	   
		$userId = is_a($userInfo,'Person') ? $userInfo->id : $userInfo['id'];
		$email = is_a($userInfo,'Person') ? $userInfo->email : $userInfo['email'];
		
		$ini = System::GetIni();
	    
	    $token = \SimpleSession::Get()->GetID();
	    
	
		$default = $_SERVER['REQUEST_URI'];
		$default = array_shift(explode('?',$default));
		$default.='media/images/anonuser';
		$default_sm =$default;
		$default = BASEURL."wapi/media/icons/action:get/target:icon/icon:Users-9/category:Icons/size:128/token:$token";
		$default_sm = BASEURL."wapi/media/icons/action:get/target:icon/icon:Users-9/category:Icons/size:64/token:$token";
		
		if(!file_exists($ini->usermedia.((int)$userId))){
			mkdir($ini->usermedia.$userId);
		}
	
		$full_png = sprintf("%s%d/usericon.png",$ini->usermedia,(int)$userId);
		$sm_png = sprintf("%s%d/usericon_sm.png",$ini->usermedia,(int)$userId);
		
		if(file_exists($full_png)) unlink($full_png);
		if(file_exists($sm_png))unlink($sm_png);
		
        $file = fopen($full_png,'w+');
        
       	fwrite($file,file_get_contents($default));
       	fclose($file);	
       	
       	$file = fopen($sm_png,'w+');
       	fwrite($file,file_get_contents($default_sm));
       	fclose($file);
	}
}

?>