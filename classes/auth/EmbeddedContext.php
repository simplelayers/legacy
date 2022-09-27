<?php

namespace auth;
use \SimpleSession;

class EmbeddedContext extends Context {
	
	public function __construct(Creds $creds ) {		
		parent::__construct($creds);
		
		set_exception_handler("wapi_exception_handler");
		#if($this->sessMethod == SimpleSession::METHOD_COOKIE) $this->sessionMethod = SimpleSession::METHOD_INVALID;
		$sys = \System::Get();
		$session = \SimpleSession::Get();
		$info = $session->GetUserInfo();
		
			if(!is_null($info) && $session->isEmbedded ) {
			$this->sessState = ($session->isEmbedded) ? SimpleSession::STATE_SESS_OK : $this->sessState;
			return;
		}
 		if(is_null($info)) {
				
			
			if(\RequestUtil::Get('do')=='wapi.secure_connection') {
				$application = \RequestUtil::Get('application');
					
				if(is_null($application)) {
					throw new \Exception('application required');
				}
				
				$user = \System::Get()->getPersonByUsername('visitor@simplelayers');
				$session->CreateSession($user,array('application'=>$application),false,false,0);
			}
			$this->sessState = SimpleSession::STATE_SESS_OK;
		}

		
	}

	public function GetApp() {
		$session = SimpleSession::Get();
		if(isset($session->application)) return $session->application;
		return '';
		
	}
	
}

?>