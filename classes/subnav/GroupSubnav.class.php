<?php

namespace subnav;

class GroupSubnav extends Subnav {
	
	function makeDefault($group, $user){
		$this->switchForOwner($group, $user);
		$this->add("View", "Details", "group.info&groupId=<!--{\$id}-->");
		if(!is_null($group->org_id)) $this->add("View", "Organization", 'organization.info&id=<!--{$org_id}-->');
		$userStatus = $group->getStatus($user->id);
		if($userStatus == 1 || $userStatus == 5 || $user->admin){
			$this->add("View", "Maps", "group.projects&id=<!--{\$id}-->");
			$this->add("View", "Layers", "group.layers&id=<!--{\$id}-->");
			$this->add("View", "Forum", "group.discussion.list&id=<!--{\$id}-->");
			if($group->moderator->id == $user->id  || $user->admin){
				$this->add("Manage", "Invite", "group.contacts&id=<!--{\$id}-->");
				$this->add("Manage", "Delete", 'javascript: if(confirm(\'Are you sure you want to delete this group?\nThere is no way to un-delete or recover a group once it has been deleted.\n\nClick OK to delete this group.\nClick Cancel to NOT delete this group.\')){window.location = \'.?do=group.delete&id='.$group->id.'\';}');
			}
		}
	}
	function switchForOwner($group, $user){
		$this->assign("id", $group->id);
		$this->assign("org_id", $group->org_id);
		$this->assign("objectData", '<a href="./?do=group.list">Groups</a>');
		switch($group->getStatus($user->id)){
			case 0:
				if($group->invite){
					$this->assign("rightbar", '<a href="./?do=group.action&action=request&group='.$group->id.'">Request</a>');
				}else{
					$this->assign("rightbar", '<a href="./?do=group.action&action=join&group='.$group->id.'">Join</a>');
				}
			break;
			case 1: $this->assign("rightbar", '<a href="./?do=group.action&action=leave&group='.$group->id.'">Leave</a>'); break;
			case 2: $this->assign("rightbar", '<a href="./?do=group.action&action=acceptinvite&group='.$group->id.'">Accept</a> - <a href="./?do=group.action&action=denyinvite&group='.$group->id.'">Deny</a>'); break;
			case 3: $this->assign("rightbar", '<a href="./?do=group.action&action=unrequest&group='.$group->id.'">Unrequest</a>'); break;
			case 4: $this->assign("rightbar", '<a href="./?do=group.action&action=leave&group='.$group->id.'">Leave</a>'); break;
			case 5: $this->assign("rightbar", '<a href="./?do=group.info&groupId='.$group->id.'">Manage</a>'); break;
		}
		$this->assign("ownerData", htmlspecialchars ($group->title));
		/*if ($user->iscontactBookmarkedById($contact->id)) {
			$this->assign("edit", '<a href="./?do=contact.removebookmark&id='.$contact->id.'"><img src="media/icons/book_delete.png"/></a>');
		}else {
			$this->assign("edit", '<a href="./?do=contact.addbookmark&id='.$contact->id.'"><img src="media/icons/book_add.png"/></a>');
		}*/
	}
}
?>