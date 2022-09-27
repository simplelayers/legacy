<?php

namespace auth;

class Auth {
	
	
	const STATE_ANON = 2;
	const STATE_OK = 1;
	const STATE_UNKNOWN = 0;
	const STATE_ERROR_NEEDPW = -1;
	const STATE_ERROR_NEED_PW_RETRIEVAL= -1.1;
	const STATE_ERROR_PW_SENT=-1.2;
	const STATE_ERROR_INVALID_CREDS= -2;
	
	
	public static function GetAuthState( $creds = null) {
		if(is_null($creds))$creds = Creds::GetFromRequest();
		
		$db = \System::GetDB(\System::DB_ACCOUNT_SU);
		$username = $creds->username;
		$password = $creds->password;
		if($password == "") $password = null;
		
		
		if(is_null($username) && is_null($password)) {
			return self::STATE_ANON;					
		} 
		
		
		if( is_null($username) && !is_null($password)) {
			return self::STATE_UNKNOWN;
		}
		
		
		
		$person =  \System::Get()->getPersonByUsername($username);
		
		if($person) {
		    
			$password = \Security::Encode_1way( $password, ( int ) $person->id );
			$check= $person->password == $password;
                
			if($check) {
				return self::STATE_OK;
			}
		}
		
		if (!is_null($username) && is_null($password)) {
			if($person->hash) {
				return self::STATE_ERROR_PW_SENT;
			}
			return self::STATE_ERROR_NEEDPW;
		}
		
		
		return self::STATE_ERROR_INVALID_CREDS;
		
	}
	
	public static function GetEnum() {
		if(isset($GLOBALS['SL_AUTH_ENUM']))return $GLOBALS['SL_AUTH_ENUM'];
		$enum = new \Enum();
		$enum->AddItem('auth_anon',self::STATE_ANON);
		$enum->AddItem('auth_invalid_creds',self::STATE_ERROR_INVALID_CREDS);
		$enum->AddItem('auth_need_pw_retrieval',self::STATE_ERROR_NEED_PW_RETRIEVAL);
		$enum->AddItem('auth_need_pw',self::STATE_ERROR_NEEDPW);
		$enum->AddItem('auth_pw_sent',self::STATE_ERROR_PW_SENT);
		$enum->AddItem('auth_ok',self::STATE_OK);
		$enum->AddItem('auth_unknown',self::STATE_UNKNOWN);
		$GLOBALS['SL_AUTH_ENUM'] =$enum;
		return $enum;		
		
	}
	
	
}

?>