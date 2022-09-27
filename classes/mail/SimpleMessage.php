<?php

namespace mail;

class SimpleMessage {
	/**
	 * @param string entry for the subject line in the outgoing message
	 * @param array 
	 */
	public static function NewMessage($subject,$senderName,$senderEmail, $message=null, $parameters=null) {
		$messageData = array();
		$messageData[SimpleMail::SENDER_NAME] = $senderName;
		$messageData[SimpleMail::SENDER_EMAIL] = $senderEmail;		
		$messageData[SimpleMail::SUBJECT] = $subject;
		$messageData[SimpleMail::MESSAGE] = (is_string($message)) ? explode("\n", $message ) : $message;
		
		if(!is_null($parameters)){
			foreach($parameters as $param=>$val) {
				if (preg_match('/([\<])([^\>]{1,})*([\>])/i', $val )) {
					$normalized = $val;	
				} else {
					$decoded = html_entity_decode($val, ENT_COMPAT, 'UTF-8');
					$normalized = htmlspecialchars($decoded, ENT_COMPAT, 'UTF-8', false);
				}
				$parameters[$param] = $normalized;
			}
			$messageData['parameters'] = $parameters;
		}
		ksort($messageData);
		
		return $messageData;
	}
		
	
}

?>