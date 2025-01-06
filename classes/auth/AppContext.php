<?php

namespace auth;

use \SimpleSession;
use utils\ParamUtil;
use apps\Application;
use utils\DOUtil;

class AppContext extends Context {
    public $app = null;
    public $params = null;
    public $isloggingIn = false;
	
	public function __construct(Creds $creds) {
		parent::__construct ( $creds );
		// Handle Exceptions through the wapi exception handler which serves as a global exception handler.
		
		set_exception_handler ( "wapi_exception_handler" );
		setcookie('sl_cookie_last_url',$_REQUEST['do'],strtotime('20 minutes'));
		
		
		$sys = \System::Get ();
		$session = \SimpleSession::Get ();
		
		$info = $session->GetUserInfo ();
	   
		$visitorId = \System::GetPublicUser(true);
		if($visitorId != $info['id'] ) {$this->isAuth=true; }
		
		if($this->isAuth) $this->authState = Auth::STATE_OK; 
		
		$doInfo = explode('/',$_REQUEST['do']);
		$this->app= array_shift($doInfo);
		$this->params = ParamUtil::ParseParams($doInfo);
		
		foreach($_REQUEST as $key=>$val) {
		    if(in_array($key,array('do','sl_path','context'))) continue;
		    
		    if(!isset($this->params[$key])) $this->params[$key] = $val;
		}
		if(isset($_GET['context'])) {
		    $this->params['context'] = $_GET['context'];
		}
		
		// OK the session state if the current info is null and the current session is embedded
		if ($session->sessionState == SimpleSession::STATE_SESS_NONE && $session->isEmbedded) {
			$this->sessState = ($session->isEmbedded) ? SimpleSession::STATE_SESS_OK : $this->sessState;
			return;		
		}
		
		// Determine if the request is for someone doing a wapi login:
		//$this->isLoggingIn = ParamUtil::Get($this->params,'do') == 'wapi.auth.authenticate';
		$this->isLoggingIn = DOUtil::Contains('wapi.auth.authenticate')  ;
		if(!$this->isLoggingIn)$this->isLoggingIn = DOUtil::Contains('auth/authenticate');
		// If there is no session create one
		
		if($this->sessionState == SimpleSession::STATE_SESS_NONE) {
		    if($this->isLoggingIn) {
				$application = $this->app;
				
				if (is_null ( $application )) {
					throw new \Exception ( 'Requested application "'.$this->app.'" not recognized.' );
				}
			     $user = \System::GetPublicUser();
				
				$session->CreateSession ( $user, array (
						'application' => $application 
				), false, false, 0 );
				
			}
			$this->sessState = SimpleSession::STATE_SESS_OK;
		}
		
	}
	public function GetApp() {
	    return $this->app;
		/*$session = SimpleSession::Get ();
		if (isset ( $session->application ))
			return $session->application;
		return '';*/
	}
	
	public function Exec($args=null) 
	{
	    
		if(is_null($args)) $args=$_REQUEST;
		//$_GET['do'] = $action;
		define('APPDIR',WEBROOT ."contexts/apps/$this->app/app");
		
		
		//require_once('contexts/apps/application.php');
		Application::Exec($this->params);
		
		
		#var_dump($path,$action);		
	}	
}
?>