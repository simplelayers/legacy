<?php
namespace model\reporting;

use mail\SimpleMessage;
use mail\SimpleMail;
class Notifications
{
    private static $_enum;
    
    const DISCUSSION_REPLY=1;
    const MAP_SHARED=2;
    const LAYER_SHARED=4;
    const MAP_EDITED=8;
    const LAYER_EDITED=16;
    const NEW_DISCUSSION=32;
    const CONTACT_FOLLOWS=64;
    const UPDATED_LAYER_DATA=128;
    const GROUP_INVITES_JOINS=256;
    const MAP_COMMENT=512;
    const LAYER_COMMENT=1024;
    const LAYER_OWNERSHIP_TRANSEFER=2048;
    
    public static function GetEnum() {
        if(self::$_enum) return self::$_enum;
        
        $_enum = new \FlagEnum();
        $_enum->AddItem('Discussion Reply');
        $_enum->AddItem('Map Shared');
        $_enum->AddItem('Layer Shared');
        $_enum->AddItem('Map Edited');
        $_enum->AddItem('Layer Edited');
        $_enum->AddItem('New Discussion');
        $_enum->AddItem('Contact Follows');
        $_enum->AddItem('Updated Layer Data');
        $_enum->AddItem('Group Membership');
        $_enum->AddItem('Map Comment');
        $_enum->AddItem('Layer Comment');
        $_enum->AddItem('Layer Ownership Transfer');
    }
    
    public static function Notify($recipientId,$actorId, $action, $subjectName, $subjectId, $redirect, $subjectType) {
        return;
        $sys = \System::Get();
        $ini = \System::GetIni();
        $recipient = $sys->getPersonById($recipientId);
        $actor = $sys->getPersonById($actorId);
        if($recipientId == $actorId) return false;
        $enum = self::GetEnum();
        $subjectTypeString = is_string($subjectType) ? $subjectType : $enum[$subjectType];
        $message = $subjectName.' '.$action;
        $simpleMessage = SimpleMessage::NewMessage("[".$ini->title.' - '.$subjectTypeString .']', $actor->realname, $actor->email,$message);
        
        SimpleMail::SendTemplatedMessage($recipient->email, $simpleMessage);
        
    }
    
    
}

?>