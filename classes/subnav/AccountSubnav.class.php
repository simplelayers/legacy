<?php
namespace subnav;

use model\Permissions;
class AccountSubnav extends Subnav {
	function makeDefault(){
		//$this->add('Organization')
		$session = \SimpleSession::Get();
		$permissions = $session['permissions'];
		$userInfo = $session->GetUserInfo();
		$this->assign("objectData", 'Account');
		$this->assign("ownerData",$userInfo['realname'].' ( <i>'.$userInfo['username'].' )</i>');
		$userInfo = \SimpleSession::Get()->GetUserInfo();
		$user = \System::Get()->getPersonById($userInfo['id']);
		if($user->organization) $this->add('View', 'Organization','organization.info'); // :Organization:Details: View		
		if($permissions[':Profile:Usage'] & Permissions::VIEW) $this->add('View', 'Account Type &amp; Usage','account.type'); //:Profile: View
		if($permissions[':Profile:'] & Permissions::EDIT) $this->add('Manage', 'Change Password','account.password1'); //:Profile: Edit
		if($permissions[':Profile:'] & Permissions::DELETE) $this->add('Manage', 'Delete your Account','account.deleteme1'); //:Profile: Delete
		if($permissions[':Profile:Usage:'] & Permissions::VIEW) $this->add('Edit', 'Your Profile','contact.info'); //:Profile: Edit
		
		
		
	}
	
}
?>