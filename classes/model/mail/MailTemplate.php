<?php

namespace model\mail;

class MailTemplate {
	
	private $_template = null;
	
	public function __construct($templateName) {
				$this->_template = $templateName;
	}
	
	public static function AddRecipient(&$recipients, $email,$name,$type='to'){
		$recipients[] = array('email'=>$email,'name'=>$name,'type'=>$type);
	}
	
	public static function AddMergeVar(&$mergeVars, $varName, $varVal) {
		$mergeVars[] = array(
				'name' => $varName,
				'content' => $varVal
		);
	}
	
	public function SendMessage($subject,$from_email,$from_name,$recipients,$reply_to,$mergeVars,$tags=null,$bcc=null) {
		if(is_null($tags)) $tags= array();
		try {
			
			$mandrill = \System::GetMandrill();
			$mandrill->debug=true;
			$template_name = $this->_template;
			$template_content = array();
					/*array(
							'name' => 'example name',
							'content' => 'example content'
					)(
			);*/
			$message = array(
					'subject' => $subject,
					'from_email' => $from_email,
					'from_name' => $from_name,
					'to' => $recipients,
					'headers' => array('Reply-To' => $reply_to),
					'important' => false,
					'track_opens' => true,
					'track_clicks' => true,
					'auto_text' => null,
					'auto_html' => null,
					'inline_css' => null,
					'url_strip_qs' => null,
					'preserve_recipients' => null,
					'view_content_link' => null,
					'bcc_address' => $bcc,
					'tracking_domain' => null,
					'signing_domain' => null,
					'return_path_domain' => null,
					'merge' => true,
					'global_merge_vars' => $mergeVars,
					'merge_vars' => array(),
					'tags' => $tags,
					'metadata' => array('website' => 'simplelayers.com'),					
			);
			$async = false;
			$ip_pool = 'Main Pool';
			$tz = date('O');
			$time = strtotime('-1 day');
			$send_at = null;//date("Y-m-d H:i:s",$time );
			
			$result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
			return $result;
			
		} catch(\Mandrill_Error $e) {
			// Mandrill errors are thrown as exceptions
			echo 'Problem sending email: ' . get_class($e) . ' - ' . $e->getMessage();
			// A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
			throw $e;
		}
		
	}
}

?>