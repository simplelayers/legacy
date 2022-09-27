<?php

namespace auth;

use \SimpleSession;
use utils\DOUtil;
use model\Permissions;
use utils\ParamUtil;

class Context {
	
	public $authState = Auth::STATE_UNKNOWN;
	public $sessState = SimpleSession::STATE_SESS_NONE;
	public $sessMethod = SimpleSession::METHOD_UNKNOWN;
	public $isRunningLocal;
	public $isEmbedded;
	public $isAuth = false;
	
	const CONTEXT_DMI = 'dmi';
	const CONTEXT_WAPI = 'wapi';
	const CONTEXT_APP = 'app';
	const CONTEXT_EMBED = 'embed';
	const CONTEXT_OPENLAYERS = 'openlayers';
	const CONTEXT_MAIL_ACTION = 'mail_action';
	
	
	protected $fromLogin = false;
	protected function __construct(Creds $creds) {
		
		$this->isRunningLocal = ! isset ( $_SERVER ['HTTP_HOST'] );
		$this->authState = Auth::GetAuthState ( $creds );
		
		/* @var $session SimpleSession */
		$session = SimpleSession::Get ();
		
	  
		
		$this->sessState = $session->sessionState;
		$this->sessMethod = $session->sessionMethod;
		
		$this->fromLogin = \RequestUtil::Get ( 'do' ) == 'account.login';
		if(!$this->fromLogin) $this->fromLogin = DOUtil::Contains( 'auth/authenticate');
		$this->isEmbedded = $session->isEmbedded;
		$this->isAuth = DOUtil::Contains ( 'wapi.auth.' );
		if(!$this->isAuth) $this->isAuth = DoUtil::Contains('auth/');
		#if(DOUtil::Contains('account.login'))$this->isAuth = true;
		#if(DOUtil::Contains('account.logout')) $this->isAuth = true;
		if (! $this->fromLogin)
			$this->fromLogin = \RequestUtil::Get ( 'do', null ) == 'wapi.secure_connection';
	}
	public function __get($what) {
		if ($what == 'authState')
			return $this->authState;
		return null;
	}
	public static function Get($creds = null, $context = null) {
		if (isset ( $GLOBALS ['SLCONTEXT'] ))
			return $GLOBALS ['SLCONTEXT'];
		
		
		if (is_null ( $creds ))
			$creds = Creds::GetFromRequest ();
		
		if(is_null($context)) {
			if( DOUtil::Contains('wapi.')) $context = 'wapi';			
		}
		
		if (is_null ( $context )) {
			$context = \RequestUtil::Get ( 'context' );
		}
		$api_cmd = ParamUtil::Get ($_REQUEST, 'do');
		$api_cmd = 	explode ( '/', $api_cmd );
		$api_cmd_segs = $api_cmd;
		$api_cmd = array();
		foreach($api_cmd_segs as $cmd) {
			if(!strpos($cmd,':')) {
				$api_cmd[] = $cmd;
				continue;
			}
			$keyval = explode(':',$cmd);
			$key = array_shift($keyval);
			$val = implode(':',$keyval);
			$_REQUEST[$key] = $val;
		}
		
		
		$session = \SimpleSession::Get ();
		
		
		$referer = isset ( $_SERVER ['HTTP_REFERER'] ) ? $_SERVER ['HTTP_REFERER'] : "";
		
		/*
		 * $isLoginPage = (stripos(DOUtil::Get(),'account.login') > -1); $isWapiLogin = (stripos(\RequestUtil::Get('do',''),'wapi')>-1); if($session->isEmbedded) { $context = new EmbeddedContext($creds); return $context; } $isAuth = DOUtil::Contains('wapi.auth.'); if($isAuth) { if(\RequestUtil::Contains('application','dmi')) { $context = new DMIContext($creds); $GLOBALS['SLCONTEXT'] = $context; return $context; } } if($isLoginPage){ $context = new DMIContext($creds); $GLOBALS['SLCONTEXT'] = $context; return $context; } elseif($isWapiLogin) { $context = new WAPIContext($creds); $GLOBALS['SLCONTEXT'] = $context; return $context; } $session = SimpleSession::Get();
		 */
		// $context = null;
		
		if (! is_null ( $context )) {
			switch ($context) {
				case self::CONTEXT_WAPI :
				    
					$context = new WAPIContext($creds);
					
					break;
				case self::CONTEXT_APP :
				case self::CONTEXT_EMBED:
					$context = new AppContext($creds);
					break;
				case self::CONTEXT_OPENLAYERS:
					$request = $_SERVER["REQUEST_URI"];
					$request = explode('/',$request);
					$offset = array_search( 'openlayers', $request);
					if($offset < count($request)-1) {
						$request = implode('/',array_slice($request,$offset+1));
						$ini = \System::GetIni();
						if(file_exists($ini->openlayers_src.$request)) {
							readfile($ini->openlayers_src.$request);
						} else {
							header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found"); 
							/*
							echo "<h1>404 Not Found</h1>";
							echo "The page that you have requested could not be found.";
							*/	
							exit();
						}
					}
					break;
				case self::CONTEXT_MAIL_ACTION:
				    $context = new MailActionContext($creds);
				    break;
				default :
				case self::CONTEXT_DMI :
					$context= new DMIContext($creds);
					break;
			}
		} else {
			if ($session->sessionMethod == SimpleSession::METHOD_TOKEN)
				$context = new WAPIContext ( $creds );
			elseif ($session->sessionMethod == SimpleSession::METHOD_COOKIE) {
				$context = new DMIContext ( $creds );
			}			
			if (is_null ( $context ))
				$context = new DMIContext ( $creds );
		
		}
		
		$GLOBALS ['SLCONTEXT'] = $context;
		
		return $context;
	}
	
	
	public function IsLoggedIn() {
		return ($this->sessState >= SimpleSession::STATE_SESS_OK) && ($this->authState == Auth::STATE_OK);
	}
	
	public function Update() {
		$this->__construct ();
	}
	
	public function GetApp() {
		return "";
	}
	
	public function GetStart() {
		return 'login.php';
	}
	
	public function EndSession() {
		$session = SimpleSession::Get ();
		$session->EndSession ();
		$this->sessState = SimpleSession::STATE_SESS_NONE;
		$this->authState = Auth::STATE_UNKNOWN;
		$this->sessMethod = SimpleSession::METHOD_UNKNOWN;
		#header ( 'Location: .?do=' . $this->GetStart () );
	}
	
	public function IsSysAdmin() {
		$session  = SimpleSession::Get();
		if(is_null($session)) {
			return false;
		}
		$result = $session->GetPermission(':SysAdmin:General:');
		$isSysAdmin = ($session->GetPermission(':SysAdmin:General:') & (Permissions::VIEW + Permissions::EDIT)) >0;

		return $isSysAdmin;// (($this->authState == SimpleSession::STATE_SESS_OK) && (( int ) ($user->id) == 0));
	}
	
	
	public static function Redirect($url) {
		header('Location: '.BASEURL.$url);
		die();
		return;	
	}
	
}

?>
