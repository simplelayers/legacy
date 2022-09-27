<?php

/**
  * A wrapper class for manipulating security tokens.
  * @package ClassHierarchy
  */
/**
  *  
  *
  * @property-read array userTokens rows from db where user is owner
  * @property int userid cartograph user id (table field)
  * @property string appName name of the application this token is good for (table field)
  * @property boolean authenticated true if token has been authenticated, else false (table field)
  * 
  * @package ClassHierarchy
  * 
  */

class TokenAuthenticator {
	/**
	  * @ignore
	  */
	private $world; // a link to the World we live in
	/**
	  * @ignore
	  */
	private $tokenRecords;
	private $tokenRecord;
	private $booleanFields;

	public $token; // the token's ID string
	public $appValid; // some authentication thing
	public $tokenValid; // some authentication thing
	public $userValid; // some authentication thing
	public $runningFromHD;
	public $user;

	public $ERR_TOKEN_INVALID = "Supplied token not valid";
	public $ERR_USER_INVALID = "User credentials not valid";
	public $ERR_APPLICATIOn_INVALID = "Application invalid for supplied token";

	/**
	 * @ignore
	 */
	function __construct(& $world) {
		$this->world = $world;
		$this->userValid = false;
		$this->appValid = false;
		$this->tokenValid = false;
		$this->booleanFields= array ('authenticated','embedded');
	}

	/**
	 * @ignore
	 */
	function __get($name) {
		//if (preg_match('/\W/',$name)) return false;

		if ($name == "userTokens") {
			if ($this->userTokens == null)
				$this->CacheUserTokens();
			$func = create_function('$record', 'return $record["id"];');
			return array_map($func, $this->tokenRecords);
		}
		
		if( $name=="tokenRecord") return $this->tokenRecord;

		if (isset ($this->tokenRecord)) {
			//error_log($name.":".var_export($this->booleanFields,true ));
			if (in_array($name, $this->booleanFields)) {
				return ($this->tokenRecord[$name] == 't') ? true : false;
			}
			if (isset ($this->tokenRecord[$name]))
				return $this->tokenRecord[$name];
		}
		return false;
	}

	/**
	 * @ignore
	 */
	function __set($name, $value) {
		// simple sanity check
		if (preg_match('/\W/', $name))
			return false;

		// sanitize boolean fields
		if (in_array($name, $this->booleanFields))
			$value = (bool) $value ? 't' : 'f';

		// save it to the DB
		$this->world->db->Execute("UPDATE tokens SET $name=?,modified=now() WHERE token=?", array ($value,$this->token));
		$err = $this->world->db->ErrorMsg();
		if ($err !== "")
			error_log("setting $name in TokenAuthenticator:$err ");
		$this->CacheToken();
	}

	/**
	 * set the userValid property based on username and password, or
	 * optionally, if the php session's username property is set and
	 * it matches the userid in the token record the user may be considered valid.
	 * @param username user name provided by user (optional)
	 * @password password provided by user (optional)
	 * @sessionUser value of $SESSION['username']
	 */
	function ValidateUser($username = null, $password = null) { //,$sessionUser=null) {
		// the various checks; Art may want to document the logic here
		
		$loggedIn = isset($_SESSION['loggedin'] ) ? $_SESSION['loggedin'] : false;
		if (!is_null($username) and !is_null($password)) {
			$this->userValid = $this->world->verifyPassword($username, $password);
			$this->user = $this->world->getPersonByUsername($username);
			$_SESSION['loggedin'] = $username;
		} else if( $loggedIn) {
				$this->user = $this->world->getPersonByUsername($_SESSION['loggedin']);
				$this->userValid = true;
				return $this->userValid;
		} elseif ($this->userValid) {
			$this->user = $this->world->getPersonByUsername($username);
			$this->CacheUserTokens();
		} elseif ($this->tokenValid) {
			$this->user = $this->world->getPersonById((int)$this->userid);
			
			$this->userValid = ($this->user !== false);
			if ($this->userValid) {
				$this->CacheUserTokens();
			}
		} else {
			$this->user = $this->world->getPersonById(0);
		}
		return $this->userValid;
	}

	/**
	 * compare the url for the current request to the http referrer
	 * The world, sandbox, and branch must be the same in both. This is more
	 * to assure distinct world-sandbox-branch application-sessions than for
	 * security purposes. Security-wise it should also keep the innocent innocent.
	 * @param url for the current request
	 * @param referrer http referrer for the calling app.
	 */
	function ValidateApp($server) {
		if (!$this->tokenValid)
			return false;
		$url = $server['REQUEST_URI'];
		$referer = $server['HTTP_REFERER'];
		
		if(stripos($referer,'file://') == 0 ) {
			$this->appValid = true;
			$this->runningFromHD = true;
		} else {
			$this->runningFromHD = false;
		}
		
		$app = @ $this->appName;
		if (!$app)
			return false;

		# check their referer
		$world = basename($server['HTTP_HOST'], '.cartograph.com');
		
		$sandbox = $url;
		preg_match("/(.+?)\/viewer\//", $url, $sandbox);
		if (sizeof($sandbox) > 0) $sandbox = $sandbox[1];

		$check1 = preg_match("/^https:\/\/{$world}\.cartograph\.com\/{$sandbox}viewer\/?do=get/", $referer);
		$check2 = preg_match("/asset={$app}/", $referer);
		if (!$check1)
			return false;
		if (!$check2)
			return false;

		// no failures? must be okay.
		return true;
	}

	/**
	 * create a token record in the db, do not set authenticated at this stage
	 * @param appName name of flex module referred to in request
	 * @param session php sessionid, there can be multiple apps sharing the same php sesison.
	 */
	function CreateToken($appname, $phpsessionid, $userid = 0, $embedded = false) {
		if ($appname == "")
			throw new Exception('tokens must be set for a valid application');
		if ($phpsessionid == "")
			throw new Exception('tokens are required to have a valid php session');

		if ($userid == 0)
			$embedded = true;
		$this->token = $this->generateTokenID();
		//$embedded = $embedded ? 't':'f';
		$embedded = $embedded ? 't' : 'f';
		$this->world->db->Execute("INSERT INTO tokens (token,userid,phpsession,application,embedded,modified,context) VALUES (?,?,?,?,?,now(),?)", array (
			$this->token,
			$userid,
			$phpsessionid,
			$appname,
			$embedded,
			$_SERVER['REMOTE_ADDR']
		));
		$this->CacheUserTokens();
		$this->userValid = true;
		$this->tokenValid = true;
		return $this->token;
	}

	/**
	 */
	function ValidateAppToken($token, $sessionId=null,$useSession = true) {
		
		if( is_null($sessionId)) $sessionId = session_id();
		$this->token = $token;
		$this->CacheToken();
		$this->tokenValid = ($this->tokenRecord !== null);
		
		if ($useSession && ($this->phpsession !== $sessionId)) {
			
			$this->tokenValid = false;
		}
		
		return $this->tokenValid;
	}

	function AuthenticateToken() {
		return;//ToDo
		if ($this->tokenValid && $this->userValid) {
			$this->authenticated = true;
		}
	}

	function CacheToken($token=null) {
		//if(!is_null($token)) $this->token = $token;
		$record = $this->world->db->GetRow('SELECT * FROM tokens WHERE token=?', array ($this->token));
		$this->tokenRecord = $record;
		//$this->tokenRecord = ($record ) ? $record[0] : null;
	}

	function CacheUserTokens() {
		if (!$this->userValid)
			return;
		$this->tokenRecords = array ();
		$this->tokenRecord = null;

		$results = $this->world->db->Execute('SELECT * FROM tokens WHERE userid=?', array (
			$this->user->id
		))->getRows();
		if ($results)
			$this->tokenRecords = $results;

	}

	function GetSessionUIDs($sessionid,$appname) {
		if ($sessionid == null) return;
		$results = $this->world->db->Execute('SELECT * FROM tokens WHERE userid <> 0 AND phpsession=? and application=? order by modified desc ', array (
			$sessionid,$appname
		));
		if ($results) {
			$results = $results->getRows();

			$func = create_function('$record', 'return $record;');
			return array_map($func, $results);
		} else {
			return array ();
		}
	}

	function RemoveToken($tokenId) {

		$results = $this->world->db->Execute('DELETE FROM tokens WHERE token=?', array (
			$tokenId
		));
		$this->CleanUp();
	}

	function CleanUp() {
		$results = $this->world->db->Execute('DELETE FROM tokens WHERE age(modified) > Interval \'1 day\'', array ());
		$results = $this->world->db->Execute('VACUUM tokens');
	}

	/*
	 * Generate a token identifier
	* @return $id A string, 32 bytes in length. Astronomically likely to be unique.
	 */
	function generateTokenID() {
		return md5(microtime() . mt_rand());
	}

}
?>
