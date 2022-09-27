<?php

namespace mail;

use model\logging\Log;
use PHPMailer\PHPMailer\PHPMailer;

class SimpleMail {

    const INVALID_TEMPLATE = 'Attempting to send mail of unrecognized type %s: failed to find template message_templates/%s.tpl';
    const INVALID_MESSAGE_DATA = 'Attempting to send mail of unrecognized type %s: failed to find template message_templates/%s.tpl';
    const MISSING_SENDER_EMAIL = 'Missing paramter sender_email';
    const MISSING_SENDER_NAME = 'Missing paramter sender_name';
    const MISSING_SUBJECT = 'Missing paramter subject';
    const ERR_MAIL_PROB = "There was a problem sending message";
    const ERR_INVALID_MSGDATA = "Message data is missing required parameters";
    const SENDER_EMAIL = 'sender_email';
    const SENDER_NAME = 'sender_name';
    const SUBJECT = 'subject';
    const MESSAGE = 'message';

    public static function SendMessage($body, $recipients, $messageData, $cc = array(), $bcc = array()) {

        if (substr($body, 0, 3) == '﻿') {
            $body = substr($body, 3);
        }

        self::VerifyMessageData($messageData);

        $slMail = self::GetSLMail();

        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }

        foreach ($recipients as $recipient) {
            if (is_array($recipient)) {
                list($email, $realname) = $recipient;
                $slMail->addAddress($email, $realname);
            } elseif (is_a($recipient, 'Person')) {
                $slMail->addAddress($recipient->email, $recipient->realname);
            } else {
                $slMail->addAddress($recipient);
            }
        }
        $ccers = [];
        foreach ($cc as $c) {
            if (is_array($c)) {
                list($email, $realname) = $c;
                $slMail->addCC($email, $realname);
            } elseif (is_a($c, 'Person')) {
                $slMail->addCC($c->email, $c->realname);
            } else {
                $slMail->addCC($c);
            }
        }
        $bccers = [];
        foreach ($bcc as $c) {
            if (is_array($c)) {
                list($email, $realname) = $c;
                $slMail->addBCC($email, $realname);
            } elseif (is_a($c, 'Person')) {
                $slMail->addBCC($c->email, $c->realname);
            } else {
                $slMail->addBCC($c);
            }
        }
        $slMail->Subject = $messageData[self::SUBJECT];
        $slMail->IsHTML(true);
        $slMail->msgHTML($body);
        $slMail->setFrom($messageData[self::SENDER_EMAIL], $messageData[self::SENDER_NAME]);
        $result = $slMail->send();
        if (!$result) {
            throw new \Exception('mail problem:' . $slMail->ErrorInfo);
        }
        return $result;

        Log::Notice("Mail - [INVITE] sent from {$messageData[self::SENDER_EMAIL]} ({$messageData['sender_name']}) to " . json_encode($info));
    }

    public static function GetSLMail($debug = false) {


        $ini = \System::GetIni();

        $mailServer = $ini->smtp_host;
        $params = [];
        $params = $config = \System::GetMailConfig();

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'error_log';
        $mail->Host = $params['host'];
        $mail->Port = 587;//$params['port'];
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';// PHPMailer::ENCRYPTION_SMTPS;
        
        $mail->Username = $params['username'];
        $mail->Password = $params['password'];
        // $mail->AuthType = 'LOGIN';
        /*$mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
         *
         */
        $mail->Timeout = 10;
        return $mail;
    }

    public static function VerifyMessageData($messageData) {
        $hasSubject = isset($messageData[self::SUBJECT]);
        $hasSenderName = isset($messageData[self::SENDER_NAME]);
        $hasSenderEmail = isset($messageData[self::SENDER_EMAIL]);
        if (!($hasSubject and $hasSenderEmail and $hasSenderName)) {
            $missing = array();
            if (!$hasSubject)
                $missing[] = self::MISSING_SUBJECT;
            if (!$hasSenderName)
                $missing[] = self::MISSING_SENDER_NAME;
            if (!$hasSenderEmail)
                $missing[] = self::MISSING_SENDER_EMAIL;
            Log::Error(new \ErrorException(self::ERR_INVALID_MSGDATA . ":\n" . implode("\n", $missing)));
        }
        return true;
    }

    public static function SendMandrillMessage($recipients, $mandrillTemplate, $messageData, $mergeVars, $cc = array(), $bcc = array()) {
        $mandrill = \System::GetMandrill();
        $templateInfo = $mandrill->templates->info($mandrillTemplate);
        $template = $templateInfo['code'];
        $template = self::MergeVars($template, $mergeVars);
        self::SendMessage($template, $recipients, $messageData, $cc, $bcc);
    }

    public static function GetMandrillTemplateVars($mandrillTemplate) {

        $matches = array();
        $regex = '/\*\|([^|]+)\|\*/';
        preg_match_all($regex, $mandrillTemplate, $matches);
        $vars = array_pop($matches);
        return $vars;
    }

    public static function MergeVars($mandrillTemplate, $mergeVars) {

        $vars = array_unique(self::GetMandrillTemplateVars($mandrillTemplate));
        foreach ($vars as $var) {
            $varSearch = "*|$var|*";
            $val = isset($mergeVars[$var]) ? "" . $mergeVars[$var] : "";
            $mandrillTemplate = str_replace($varSearch, $val, $mandrillTemplate);
        }
        return $mandrillTemplate;
    }

    public static function SendTemplatedMessage($recipients, $messageData, $templateName = null) {

        self::VerifyMessageData($messageData);

        $template = new \SLSmarty();

        foreach ($messageData as $key => $val) {
            $template->assign($key, $val);
        }

        $logo = 'https://secure.simplelayers.com/logo.php';


        // $tpl = $templateName;

        if (is_null($templateName)) {

            $tpl = dirname(__FILE__) . "/message_templates/message_body.php";
        }

        if (!file_exists($tpl)) {
            throw new \Exception(sprintf(self::INVALID_TEMPLATE, $templateName, $templateName));
        }
        ob_start();
        include $tpl;
        $body = ob_get_clean();
        // $body = $template->fetch($tpl);


        self::SendMessage($body, $recipients, $messageData);
    }

    public static function GetEmailTarget($displayName, $email) {
        return sprintf("%s <%s>", $displayName, $email);
    }

    private static function Santitze($value) {
        return preg_replace('/[^\w\-\. \,]/', ' ', $value);
    }

    public static function AddRecipient($email, $realname, &$recipients) {
        $recipients[] = array($email, $realname);
    }

}

?>