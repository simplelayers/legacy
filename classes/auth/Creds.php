<?php

namespace auth;

class Creds {
	
	public $username=null;
	public $password=null;
	
	public function __construct($user=null,$pass=null) {
		
		$this->username = $user;
		$this->password = $pass;
	}
	
	public function IsSysOwner() {
		$ini = \System::GetIni();
		return $this->username == $ini->system_owner;
	}
	
	public function IsNull() {
		if(trim($this->username=='')) $this->username = null;
		if(trim($this->password=='')) $this->password =null;
		return is_null($this->username) || is_null($this->password);
	}
	
	public static function GetFromRequest() {
		$user = isset($_REQUEST['username']) ? $_REQUEST['username'] : null;
		if($user) $user = strtolower($user);
                $pw = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;
		$creds = new self($user,$pw);
		return $creds;
	}
	
	
	
}

?>
