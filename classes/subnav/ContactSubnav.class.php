<?php

namespace subnav;

class ContactSubnav extends Subnav {
	function makeDefault($contact, $user){
		$this->switchForOwner($contact, $user);
		#$this->add("", "Details", "contact.info&id=<!--{\$id}-->");
		if($user->admin){
			$this->add("", "Edit", "admin.edituser1&id=<!--{\$id}-->");
			$this->add("", "Disk Usage", "admin.showusage&id=<!--{\$id}-->");
		}
		//$this->add("", "Message", "wapi.contact.message&action=openmessage&recipient=<!--{\$id}-->");
	}
	function switchForOwner($contact, $user){
		$this->assign("id", $contact->id);
		$this->assign("objectData", htmlspecialchars ($contact->realname));
		$this->assign("ownerData", htmlspecialchars ($contact->username)." &mdash; ".$contact->seatname);
		if ($user->buddylist->isOnListById($contact->id)) {
			$this->assign("edit", '<a href="./?do=contact.remove&id='.$contact->id.'"><img src="media/icons/user_delete.png"/></a>');
		}else {
			$this->assign("edit", '<a href="./?do=contact.add&id='.$contact->id.'"><img src="media/icons/user_add.png"/></a>');
		}
	}
}
?>