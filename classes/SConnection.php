<?php
class SConnection {
	
	private $world;
	protected $userValid = false;
	protected $loggedIn = false;
	protected $token = null;
	protected $embedded = false;
	protected $tokenData = null;
	protected $uids = array ();
	protected $tokenExisted = false;
	protected $user;
	protected $passwordValid = false;
		
	
	public function __construct(World $world, $embedded) {
		$this->embedded = $embedded;
		$this->world = $world;
		$this->GetSessionState();
	}
	
	public function __get($target) {
		switch($target) {
			case 'isAnonymous':
				$id = (isset($this->userid)) ? $this->userid : null; 
				return( (($id===0) && ($this->loggedIn===false)) || $this->embedded);
				break;
		}
		if($this->tokenData) { 
			if(isset($this->tokenData[$target])) return $this->tokenData[$target];
		}
		return $this->$target;
		
	}
	
	protected function SetSessionState($value) {
		if($value===false) {
			unset($_SESSION['loggedin']);
		} else {
			$_SESSION['loggedin'] = $value;
		}
		
		return $this->GetSessionState();
		
	}
	
	protected function GetSessionState() {
		
		$user = isset ( $_SESSION['loggedin'] ) ? $_SESSION['loggedin'] : false;

		if ($user) {
			$this->loggedIn = true;
			$this->user = $this->world->getPersonByUsername ( $user );
			
		} else {
			$this->loggedIn = false;
			$this->user = $this->world->getPersonById ( 0 );
		}
		return $this->loggedIn;
	}
	
	public function LoginUserPass($username, $password, $app = "dmi",$force=false) {
		// Step 1:  Do basic authentication
		$this->userValid = $this->world->verifyPassword ( $username, $password );//true;
		$loggedInUser =$this->user;
		
		$this->user = $this->world->getPersonByUsername($username);

		
		$needToken = false;
		$elsewhereCheck = true;
		// Fork 1: was the user valid?
		if( $this->userValid ) // Fork 1.yes 
		{
			$this->passwordValid = true;
			//Fork 1.1: Session has a user?
			if ($this->GetSessionState() ) { // Fork 1.1.yes
				// Fork.1.1.1 = new user is session user?
				if ($username == $loggedInUser->username) { // Fork.1.1.1.yes
					$this->world->db->GetRow("delete from tokens where phpsession=? and application='dmi'",array(session_id()));
					$elsewhereCheck = false;
					$needToken = true;
					if( $app == "dmi") {
						$this->tokenData = $this->world->db->GetRow("select * from tokens where userid=? and phpsession=? and application='dmi'",array($this->user->id,session_id()));
						// Fork 1.1.1.1 Has session token
						if( $this->tokenData ) {
							$this->token = $this->tokenData['token'];
							$this->userValid = true;
							$needToken = false;
							$elsewhereCheck = false;
						} else {
							$needToken = true;
						}
					}
					
				} else { // Fork.1.1.1.no
					if( $app == 'dmi') {
						$this->world->db->GetRow("delete from tokens where phpsession=? and application='dmi'",array(session_id()));
						$this->SetSessionState($username);
						$needToken = true;
					}	
				}
			} else { //Fork 1.1.no
				$this->SetSessionState( $username );
				$this->userValid = true;
				$needToken = true;
			}
			
			if( ($app=="dmi") && $elsewhereCheck){
				// Fork 1.2: logged in somewhere else?
				
				if( $this->IsLoggedInElsewhere($app)) { // Fork 1.2.yes
					$this->userValid = false;
					$this->tokenExisted = true;
					$needToken = false;
					// Fork 1.2.1: Force login?
					if( $force ) { // Fork 1.2.1.yes
						$this->userValid = true;
						$this->world->db->Execute("delete from tokens where userid=? and application='dmi'", array($this->user->id));
						$needToken = true;
					} else { // Fork 1.2.1.no
						return false;
					}
				} else { // Fork 1.2.no
					//continue
				}
			}
			// Fork 1.3: Need token?
			if( $needToken ) { //Fork 1.3.yes
				$this->CreateToken ( $app, $this->embedded );
				$_SESSION [$this->world->name] ['token'] = $this->token;
		
			} else { // Fork 1.3.no
				// continue;
			}

	
		} else { // Fork 1.no
			$this->SetSessionState(false);
			if( $app == 'dmi') {
				$this->world->db->Execute("drop from tokens where userid=? and application='dmi' and phpsession=?".array($this->user->id,session_id()));
			}
			$this->userValid = false;
			$this->token = null;
			$this->tokenData = null;
			return false;
		}	
		
	}
	
	public function IsLoggedInElsewhere($app) {
		$phpsessionid = session_id ();
		if (($app == "dmi") && ($this->user->id != 0)) {
			$uids = $this->GetAppTokens ( $app );
			switch (count ( $uids )) {
				case 1 : // A token exists for a user other than public for application dmi
					if (($uids [0] ['phpsession'] == $phpsessionid) && $uids [0] ['userid'] == $this->user->id) {
						// the user is logging back into the same session, use the existing token
						// rather than create a new one.
						$this->token = $uids [0] ['token'];
						$this->tokenExisted = false;
					} else {
						// Trying to login from another browser/computer
						$this->tokenExisted = true;
					}
					break;
				case 0 : // No dmi tokens exist for the user
					$this->tokenExisted = false;
					break;
				default : // more than one dmi tokens exist 
					$this->tokenExisted = true;
					break;
			
			}
		} else {
			$this->tokenExisted = false;
		
		}
		return $this->tokenExisted;
	
	}
	
	public function LoginToken($token,$useSession=FALSE) {
		//$this->loggedIn = false;
		$tokenValid =  $this->world->auth->ValidateAppToken ($token,NULL,false);
		
		$tokenRecord =  $this->world->auth->tokenRecord;
		
		if(count( $tokenRecord ) > 0 ){
			$isDMI = $tokenRecord['application'] == "dmi";
			if(isset($isDMI) && ($isDMI && $useSession)) $tokenValid == $tokenValid && $token['session'] = session_id();
		} else {
			$tokenValid = false;
		}
		
		if(!$tokenValid) return false;
		
		//Fork 1: Is tokenValid?
		if( $tokenValid ) { //Fork 1.yes
			$this->token = $this->world->auth->token;
			$this->tokenData= $tokenRecord;
			$this->user = $this->world->getPersonById($this->tokenData['userid']); //($this->tokenData['userid'] == 0) ? null : 
			$this->userValid = true;
		
		} else { //Fork 1.no
				$this->userValid = false;
				$this->token = null;
				$this->tokenRecord = null;
		}
		return $this->userValid;
		
	
	}
	
	public function CreateToken($appname = "dmi", $embedded = false) {
		$phpsessionid = $_SESSION->GetID();
		
		if ($embedded) {
			$this->user = $this->world->getPersonById ( 0 );
			$this->token = $this->world->auth->CreateToken ( $appname, $phpsessionid, 0, $embedded );
		} else {
			$this->token = $this->world->auth->CreateToken ( $appname, $phpsessionid, $this->user->id, false );
		}
		
		$this->world->auth->ValidateUser ();
		$this->world->auth->ValidateApp ( $_SERVER );
		if ($appname == 'dmi') {
			$this->world->auth->authenticated = 't';
		}
		$this->tokenData = $this->world->auth->tokenRecord;
		
		return $this->token;
	}
	
	public function ConnectToSession($app="dmi") {
		if(isset($_REQUEST['token'] )) {
			return $this->LoginToken($_REQUEST['token']);
		}
		
		if($app == "dmi" ) {
			if( !$this->IsLoggedInElsewhere($app)) {
				
				if( isset( $_SESSION [$this->world->name] ['token'] ) ) {
					
					$this->LoginToken($_SESSION [$this->world->name] ['token']);
				}
			} else {
				$this->Logout($app);
			}
		} else {
			$this->CreateToken($app,$this->embedded);
			$this->token = $this->world->auth->token;
			$this->tokenData = $this->world->auth->tokenData;
			$this->userValid = true;
		}
		
		return $this->userValid;
	}
	
	public function Logout($app="dmi") {
		
		$token  = ($app == "dmi") ? "" : " and token='{$this->token}'";
		//$this->world->db->debug=true;
		$this->world->db->Execute("delete from tokens where phpsession=? and application=? $token",array(session_id(),$app));
		//$this->world->db->debug=false;
		$this->user = null;
		$this->token = null;
		$this->tokenData = null;
		$this->loggedIn = false;
		$this->tokenExisted = false;
		$this->userValid = false;
		$this->uids = false;
		$this->SetSessionState ( false );
		unset($_SESSION['token'] );
		
	}
	
	
	public function LogoutToken($token = null) {
		if (is_null ( $token ))
			return;
		$phpsessionid = session_id ();
		$this->world->auth->RemoveToken ( $token );
	
	}
	
	protected function GetSessionAppTokens($app="dmi") {
		$results = $this->world->db->Execute ( 'SELECT * FROM tokens WHERE userid=? AND application=? and phpsession=? order by modified desc', array ($this->user->id,$app,session_id() ) );
		if($results) {
			return $results->GetRows();
		}
		return array();
	}
	
	protected function GetAppTokens($appname) {
		
		$results = $this->world->db->Execute ( 'SELECT * FROM tokens WHERE userid <> 0 AND application=? AND userid=? order by modified desc', array ($appname,$this->user->id ) );
		if ($results) {
			$results = $results->getRows ();
			$func = create_function ( '$record', 'return $record;' );
			return array_map ( $func, $results );
		} else {
			return array ();
		}
	}

}

?>