<?php


use utils\ParamUtil;
use auth\Creds;
use auth\Context;
function _exec($args,$template) {

  $mail_context = array_shift(explode('/',ParamUtil::Get($args,'do')));
  $senderId = ParamUtil::Get($args,'sender');
  $sender = ($senderId) ? Person::Get($senderId) : null;
  
  $password = ParamUtil::Get($_POST,'password');
 
  
  
  // if there is noo password arg then show page with information and password input field.
     
  $orgId = Organization::GetOrgByUserId($sender->id,true);
  
  $template->assign('logo',BASEURL."logo.php?orgId=$orgId");
  
  
  if(is_null($sender)) {
      throw new Exception('no sender');
  }
  
  $template->assign('form_action',BASEURL.'mail_action/'.ParamUtil::Get($args,'do'));
  
  $actor = $sender;
  $action = ParamUtil::Get($args,'action');
  $groupId = ParamUtil::Get($args,'group');
  $group = Group::GetGroup($groupId);
 
  
  switch($action) {
      case 'acceptrequest':
      case 'denyrequest':
          $actor = $group->moderator;
        break;     
  }
  $template->assign('actor',$actor);
  if(!is_null($password)) {
      
      
      
      $creds = new Creds($sender->username,$password);
      $authState = auth\Auth::GetAuthState($creds);
      $enum = auth\Auth::GetEnum();
      switch($authState) {
          case auth\Auth::STATE_ERROR_INVALID_CREDS:
              $password = null;
              break;
          case auth\Auth::STATE_OK:
              break;
      }
      $template->assign('authState',$enum[$authState]);
  }
   $template->assign('need_password',is_null($password));
  
   
   $isModerator = $group->isModerator($actor->id);
 
      
   
   $message = "";
   if($password) {
       switch($action) {
           case 'acceptinvite': 
               $group->acceptInviteById($senderId, true);
               $message = "Your decision to accept the invitation to join group ".$group->title." has been processed. The moderator has been notified. You are now a member of the group.";
               break;
           case 'denyinvite': 
               $group->denyInviteById($senderId, true);
               $message = "Your decision to decline the invitation to join group ".$group->title." has been processed. The moderator has been notified."; 
               break;
           case 'acceptrequest':               
               if($isModerator) {
                   $group->acceptRequestById($senderId, true);
                   $message = "Your decision to accept ".$sender->realname."'s request to join group ".$group->title." has been processed. An email regarding your decision has been sent to ".$sender->realname.".";
               }           
               break;
           case 'denyrequest': 
               if($isModerator){
                   $group->denyRequestById($senderId, true);
                   $message ="Your decision to deny ".$sender->realname."'s request to join group ".$group->title." has been processed. An email regarding your decision has been sent to  ".$sender->realname.".";
                } 
                break;
       }
   }
   $template->assign('message',$message);
   $template->display('mail_action.tpl');
  // otherwise confirm the password with the sender user id
  // if not valid redisplay page with password input.
  // if valid process form data and perform action.
  
    
    
}