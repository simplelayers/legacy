<?php

namespace auth;
use \SimpleSession;
use utils\ParamUtil;
use utils\DOUtil;

class WAPIContext extends Context {
	const VERSION_CURRENT = 'v4';
	const VERSION_LEGACY = 'v3';
	private $isLoggingIn= false;
	
	public $version;
	
	public static function GetWAPIIni() {
	    $iniFile = dirname(__FILE__) .'/wapi.ini';
	    $ini = new \SimpleIni(false,$iniFile);
	    return $ini;
	}
	
	public function __construct(Creds $creds ) {		
		parent::__construct($creds);
		$session = \SimpleSession::Get();
		
		$this->isLoggingIn = DOUtil::Contains('wapi.auth.authenticate')  ;
        if(!$this->isLoggingIn)$this->isLoggingIn = DOUtil::Contains('auth/authenticate');
		if(!$this->isLoggingIn) $this->isLoggingIn = DOUtil::Contains('organization/disclaimer');
		if(!$this->isLoggingIn) $this->isLoggingIn = DOUtil::Contains('wapi.secure_connection');
		
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST');
		header("Access-Control-Allow-Headers: X-Requested-With");
		
		
		
		set_exception_handler("wapi_exception_handler");
		#if($this->sessMethod == SimpleSession::METHOD_COOKIE) $this->sessionMethod = SimpleSession::METHOD_INVALID;
		$sys = \System::Get();
        $wapiIni = self::GetWAPIIni();
        
        $isPubAccessible = in_array(DOUtil::Get(),$wapiIni->pub_exceptions);
        
        if(in_array($this->sessState ,array( SimpleSession::STATE_SESS_NONE,SimpleSession::STATE_SESS_EXPIRED)) ) {
		    /*if(is_null(\RequestUtil::Get('token',null)) && !$session) {
				throw new \Exception("Need Login");
				die();
			}*/
            
	       if(!$this->isLoggingIn) {
	           
	           if(!$isPubAccessible) {           
			     throw new \Exception("Need Login");
	           }
		   }
		}
		if($this->authState > 0) {
			
			//$session = SimpleSession::Get();
			if($session->sessionState == SimpleSession::STATE_SESS_OK && $this->authState == Auth::STATE_ANON) {
				$user = $session->GetUser();
				
				if(is_null($user)) {
					$user = $sys->getPersonByUsername('visitor@simplelayers');
				} else {
					$this->authState = Auth::STATE_OK;
				}
			} else {
				$user = $sys->getPersonByUsername($creds->username);
			}
		      
			if(!$session->HasSession() && !$isPubAccessible) {
				
				$application = \RequestUtil::Get('application');
				
				if(is_null($application)) {
					   throw new \Exception('application required');
				}
				$override = $this->fromLogin;
				$exp = $session->isEmbedded ? 0 : null;
				
				if(!$user) $user = null;
				if(!is_null($user)) {
				    
				    $session->CreateSession($user,array('application'=>$application),null,$override,$exp);
				    
				    if(!$session->isEmbedded) {
				       $session->SetCookie();
				    }
					
				}
				
			}
			
			$this->sessState = $session->sessionState;
		}
		\RequestUtil::Set('token',$session->GetID());
	}

	public function GetApp() {
		$session = SimpleSession::Get();
		if(isset($session->application)) return $session->application;
		return '';
		
	}
	
	public function Exec(array $args=null) {
		
		if(is_null($args)) $args = $_REQUEST;
		
		
		$api_cmd = ParamUtil::Get ($args, 'do');
		$api_cmdParts = explode(':',$api_cmd);
		$cmd1 = array_shift($api_cmdParts);
		
		
		$contextParam = ParamUtil::Get ($args, 'context' );
		
		if (strpos ( $cmd1, '.' )) {
			$api_cmd = explode ( '.', $api_cmd );
			$args['version'] = 'v3';
		} else {
			$api_cmd = explode ( '/', $api_cmd );
		}
			
		if ($api_cmd [0] == 'wapi') {
			array_shift ( $api_cmd );
		} else {
			if (! is_null ( $contextParam )) {
				if ($contextParam != 'wapi')
					return false;
			}
		}
		
		$cmd = array();
		foreach($api_cmd as $c) {
			//var_dump($c);
			if(stripos($c,':')===false) {
				$cmd[] = array_shift($api_cmd);
			} else {
				break;
			}			
		}
		$api_cmd = array_slice($cmd,0);
		$versions = array( self::VERSION_CURRENT , self::VERSION_LEGACY);
		$version = ParamUtil::Get ( $args, 'version', ($contextParam == 'wapi') ? self::VERSION_CURRENT : self::VERSION_LEGACY );
		if(!in_array($version ,$versions )) $version = ($contextParam == 'wapi') ? self::VERSION_CURRENT : self::VERSION_LEGACY;
		  
		if(!in_array($api_cmd[0],array(self::VERSION_CURRENT,self::VERSION_LEGACY))) array_unshift($api_cmd,$version);;
		
		
		
		//$cmd = array_pop ( array_slice ( $api_cmd, - 1 ) );
		
		$path = BASEDIR . "contexts/wapi/".implode ( "/", $api_cmd ) . '.php';
		
		if(!file_exists($path) && ($version == self::VERSION_CURRENT) ) {
			
			$version = self::VERSION_LEGACY;
			$api_cmd[0] = $version;
			$path = BASEDIR . "contexts/wapi/".implode ( "/", $api_cmd ) . '.php';
		}

		
		if (! file_exists ( $path )) {
			throw new \Exception ( "The requested command is not available for $version:".var_export($api_cmd,true) );
		}
		$this->version = $version;
		require_once ($path);
		$funcPrefix = ($version=='v4') ? '_exec' : '_dispatch_';
		
		
			
		$templater = new \SLSmarty(WEBROOT.'/contexts/wapi/templates/');
		if($version == 'v3') {
			$args['user'] = \SimpleSession::Get()->GetUser();
			$args['world'] = \System::Get();
			$cmd = implode('',array_slice($api_cmd,-1,1));
			
			\RequestUtil::Merge($args);
			return call_user_func ( $funcPrefix . $cmd,$templater,$args);
		} else {
			\RequestUtil::Merge($args);
			return call_user_func ( '_exec',$templater,$args);
		}
		
	}
	
}

?>
