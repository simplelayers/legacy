<?php

namespace utils;

use mail\SimpleMail;
use mail\SimpleMessage;
class AccountUtil {
	
	public static function ForgotPassword($login) {
		
		
		
		// generate a hash for this password change request and store it in the DB
		$ini = \System::GetIni();
		$user = \System::Get()->getPersonByUsername($login);
		
		
		
		$hash = substr(hash("sha256", microtime() . $login . mt_rand() ), 0, 32);
		$user->resetpassword = $hash;
		
		$mailData = array();
		$mailData['sender_name'] = $ini->sys_email_username;
		$mailData['sender_email'] = $ini->no_replay_email;
		
		$recipients = array($user);
		
		$url = BASEURL.'?do=account.forgotpassword3&hash='.$hash.'&id='.$user->id;
		
		$link = '<a href="'.$url.'">Click to recover your account</a>';
		
		$subject = 'Simplelayers Account Recovery for '.$user->realname;
		
		$params['Reset Link'] = $link;
		
		$messageText[] = "A request to reset your SimpleLayers  password was recieved. If you wish to reset your password click the link above.";
		$messageText[] = "If you did not request an a password reset you may disreguard this message; your account and password have not been changed in any way.";
		
		$messageData = SimpleMessage::NewMessage($subject,$ini->sys_email_username,$ini->no_reply_email, $messageText, $params);
		$mail = new SimpleMail();
		
		
		$mail->SendTemplatedMessage($recipients, $messageData);
		/*
		 * 
		 *#$hash = substr(hash("sha256", microtime() . $_REQUEST['username'] . mt_rand() ), 0, 32);		
		 * 
		 * $from     = sprintf("From: %s <%s>\r\n", $fromUser->realname, $fromUser->email );
		
		$to       = sprintf("%s <%s>", $p->realname, $p->email );
		$email = new SLSmarty();
		$message = '';
		//$devpath = '~doug/simplelayers/';
		$subject  = sprintf("Simplelayers Account Recovery for %s", $p->realname );
		$message .= sprintf("You (%s) have requested your account's password be reset. Click the link above to reset your password and recover your account.<br/><br/>If you did not request an account recovery you may disreguard this message. Your account and password have not been changed in anyway.", $p->username);
		$email->assign('url', 'account.forgotpassword3&hash='.$hash.'&id='.$p->id);
		$email->assign('urltitle', 'Click to recover your account.');
		$email->assign('subject', $subject);
		$email->assign('message', $message);
		$email->assign('devpath', $devpath);
		echo $to.' - '.$from.' - '.$subject.' - '.$email->fetch('account/email.tpl');
		$postoffice = new SimpleMail();
		$messageData = array();
		$messageData['subject'] = $subject;
		$messageData['message'] = $message;
		$mesasgeData['params'][] = 'account.forgotpassword3&hash='.$hash.'&id='.$p->id;
		$messageData['sender_email'] = 'system@simplelayers.com';
		$messageData['sender_name'] = 'System';
		*/
	}
	
	
}

?>