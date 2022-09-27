<?php
namespace auth;


class MailActionContext extends Context
{

    public function __construct(Creds $creds)
    {
       // parent::__construct($creds);
        set_exception_handler("wapi_exception_handler");
        
    }

    public function GetApp()
    {
        return '';
    }
    
    public function Exec(array $args=null) {
       
        $template = new \SLSmarty(BASEDIR.'contexts/mail_action/templates/');
        
        require_once(BASEDIR.'contexts/mail_action/actions.php');
        call_user_func('_exec',$args,$template);
        foreach($args as $argName=>$argValue) {
            $template->assign($argName,$argValue);
        }
        
        
        /*case 'invite': if($isModerator) $group->inviteById($_REQUEST['userid'], true); break;
        case 'uninvite': if($isModerator) $group->uninviteById($_REQUEST['userid'], true); break;
        case 'acceptinvite': $group->acceptInviteById($user->id, true); break;
        case 'denyinvite': $group->denyInviteById($user->id, true); break;
        case 'acceptrequest': if($isModerator) $group->acceptRequestById($_REQUEST['userid'], true); break;
        case 'denyrequest': if($isModerator){$group->denyRequestById($_REQUEST['userid'], true);} break;
        case 'join': $group->joinById($user->id, true); break;
        case 'request': $group->requestById($user->id, true); break;
        case 'unrequest': $group->unrequestById($user->id, true); break;
        case 'kick': if($isModerator) $group->kickById($_REQUEST['userid'], true); break;
        case 'leave': $group->leaveById($user->id, true); break;
        echo "execing mail action";
        */
        die();
    }
    
}

?>