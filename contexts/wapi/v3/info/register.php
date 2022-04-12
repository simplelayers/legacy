<?php

use mail\SimpleMail;
use model\InterestedParties;
use mail\SimpleMessage;
use auth\Context;
System::RequireReporting();

function _config_register() {
	
	$config = Array();
	$wapi = System::GetWapi();
	$wapi->DecorateConfig($config);
	
	// Stop config
	return $config;
}

function _headers_register() {
	
}

/**
 * @ignore
*/
function _dispatch_register($template, $args) {
	
	$context = Context::Get();
	if(!$context->IsSysAdmin()) {
		
		$template->assign('ok','Error');
		$template->assign('message','Operation not permitted with your user level');
		$template->display('wapi/okno.tpl');
	}
	
	
	$ini =System::GetIni();
	$error = false;
	$name = RequestUtil::Get('name');
	$email = RequestUtil::Get('email');
	$organization = RequestUtil::Get('organization','');
	$comments = RequestUtil::Get('comments','');
	$subject = "Request for more information";
	$ipaddress = RequestUtil::Get('ipaddress','');
	$missing = array();
	if(is_null($name)) $missing[]='name';
	if(is_null($email)) $missing[]='email';
	
	if(count($missing)) throw new Exception("Registration Error: Missing parameters\n".implode("\n",$missing));
	
	$parties = new InterestedParties();
	
	$info = $parties->Create($name,$email,$ipaddress,$organization,$comments);
	$messageParams = $info['params'];
	if(isset($messageParams['ipaddress'])) {
		$messageParams['ipaddress'] = '<a href="http://ipaddress.is/'.$messageParams['ipaddress'].'">'.$messageParams['ipaddress'].'</a>';
	}
	if(isset($messageParams['email'])) {
		$messageParams['email'] = '<a href="mailto:'.$messageParams['email'].'">'.$messageParams['email'].'</a>';
	}
	$messageParams['subject'] = $subject;
	#unset($messageParams['name']);
	unset($messageParams['subject']);
	
	$mailParams = SimpleMessage::NewMessage($subject,$name,$email,'',$messageParams);
	$recipients = $ini->demoreg_to;
	SimpleMail::SendTemplatedMessage($recipients, $mailParams);
	
	$template->display('wapi/okno.tpl');
	
	
}

?>