<?php

namespace auth;



class LoginMessages {
	const STATE_EXPIRED = 'expired';
	const STATE_EXISTS = 'exists';
	const STATE_INVALID_CREDS = 'invalid_creds';
	const STATE_NEED_PW = 'needpw';
	const STATE_FORGOT_PW = 'forgotpw';
	const STATE_PW_SENT = 'pwsent';
	const STATE_NORMAL = 'normal';
	const STATE_AUTHENTICATED = 'authenticated';
	static $stateStrings = array (
			self::STATE_EXPIRED => array (
					'messageHeader' => 'Session Expired',
					'message' => "No session activity detected in over an hour. Please log in again." 
			),
			self::STATE_EXISTS => array (
					'messageHeader' => 'Simultaneous session attempted',
					'message' => 'It appears that someone is currently logged into an active as user {%username%}. If you wish to continue logging in as %username% click <i>Continue</i> to end other sessions or <i>Cancel</i> to login with a different account. ' 
			),
			self::STATE_INVALID_CREDS => array (
					'messageHeader' => 'Incorrect Credentials',
					'message' => 'The username and password provided were not recognized.' 
			),
			self::STATE_NEED_PW => array (
					'messageHeader' => 'Password required',
					'message' => 'A password is needed to log in.' 
			),
			self::STATE_FORGOT_PW => array (
					'messageHeader' => 'Password Reset',
					'message' => 'Enter your username below and an email will be sent to you with further instructions.' 
			),
			self::STATE_PW_SENT => array (
					'messageHeader' => 'Password email sent',
					'message' => 'After resetting your password, or if you remember your password, please enter a username and password to continue.' 
			),
			self::STATE_NORMAL => array (
					'messageHeader' => 'Welcome to SimpleLayers',
					'message' => 'Enter a username and password to continue.' 
			),
			self::STATE_AUTHENTICATED => array (
			    'messageHeader' => 'Welcome to SimpleLayers',
			    'message' => 'Application authenticated.'
			)
	);
	public static function SetLoginMessages($authState, $sessState, &$params) {
		$states = array_keys ( self::$stateStrings );
		$info = null;
		$state = null;
		if ($sessState == \SimpleSession::STATE_SESS_EXISTS) {
			$state = self::STATE_EXISTS;
		}
		if ($sessState == \SimpleSession::STATE_SESS_EXPIRED) {
			$state = self::STATE_EXPIRED;
		}
		if (! $state) {
			$start = 'account/login';
			
			switch ($authState) {
				case Auth::STATE_ERROR_INVALID_CREDS :
					$state = self::STATE_INVALID_CREDS;
					break;
				case Auth::STATE_ERROR_NEEDPW :
					$state = self::STATE_NEED_PW;
					break;
				case Auth::STATE_ERROR_NEED_PW_RETRIEVAL :
					$state = self::STATE_FORGOT_PW;
					break;
				case Auth::STATE_ERROR_PW_SENT :
					$state = self::STATE_PW_SENT;
					break;
				case Auth::STATE_OK:
				    $state = self::STATE_AUTHENTICATED;
				    break;
				case Auth::STATE_UNKNOWN :
				default :
					$state = self::STATE_NORMAL;
					break;
			}
		}
		$info = self::$stateStrings [$state];
		
		foreach ( $info as $key => $val ) {
			$user = \SimpleSession::Get ()->GetUserInfo ();
			
			if ($user) {
				$name = $user ['username'];
				$val = str_replace ( '%username%', $name, $val );
			}
			
			$params [$key] = $val;
		}
		$params ['state'] = $state;
	
	}
}

?>