<?php
use mail\SimpleMessage;
use mail\SimpleMail;
/**
  * See the SocialGroup class documentation.
  * @package ClassHierarchy
  */

/**
 * The class representing a SocialGroup. This is an informal group, used to express common interests.
 * The primary effect it has on the rest of the system is Layer::getPermissionById() and Project::getPermissionById()
 * where a group's permissions are taken into account.
 * 
 * SocialGroup instances are accessed via World's methods, eg $world->listSocialGroups()
 * 
 * Public Attributes:
 * - id -- the unique ID# for this SocialGroup
 * - moderator -- Person object, the Person who has moderator privileges over the group
 * - title -- a string title for the group
 * - description -- a string description for the group
 * - hidden -- Boolean; whether the SocialGroup should be hidden from searches.
 * - invite -- Boolean; whether joining the group requires moderator approval.
 * 
 * @package ClassHierarchy
 */

class SocialGroup {
   /**
     * @ignore
     */
   private $world;
   /**
     * @ignore
     */
   public $id;
   /**
     * @ignore
     */
   function __construct(&$world,$id) {
      $this->world  = $world;
      $this->id     = $id;
   }

   ///// 
   ///// make attributes directly fetchable and editable
   /////
   /**
    * @ignore
    */
   function __get($name) {
      // simple sanity check
      if (preg_match('/\W/',$name)) return false;

      // fetch the value from the DB
      $value = $this->world->db->Execute("SELECT $name AS value FROM socialgroups WHERE id=?", array($this->id) );
      if (!$value) return null;
      $value = $value->fields['value'];

      // false attributes
      if ($name=='moderator') $value = $this->world->getPersonById($value);

      // boolean casts
      if (in_array($name,array('hidden','invite'))) $value = $value=='t';

      // return whatever it was!
      return $value;
   }
   /**
    * @ignore
    */
   function __set($name,$value) {
      // simple sanity check
      if (preg_match('/\W/',$name)) return false;
      // a few items cannot be set
      if ($name=='id') return false;

      // sanitize strings
     // if (in_array($name,array('title','description'))) $value = $value;

      // boolean casts
      if (in_array($name,array('hidden','invite'))) $value = $value ? 't' : 'f';

      // set the now-mundane attribute
      $this->world->db->Execute("UPDATE socialgroups SET $name=? WHERE id=?", array($value,$this->id) );
   }

   /** 
     * The self-destruct button, to have the group gracefully delete itself and dependencies.
     */
   function delete() {
      $this->world->db->Execute('DELETE FROM socialgroups WHERE id=?', array($this->id) );
   }


   /////
   ///// adding and removing members (Person references), and querying the list of members
   /////
   /**
     * Fetch a list of Person objects, them being members of this group.
     */
   function getMembers() {
      $people = $this->world->db->Execute('SELECT g.person_id AS id FROM socialgroups_people g, people p WHERE g.group_id=? AND p.id=g.person_id ORDER BY p.realname', array($this->id) )->getRows();
      $people = array_map( create_function('$a','return $a["id"];') , $people );
      $people = array_map( array($this->world,'getPersonById') , $people );
      return $people;
   }
   /**
     * Add a Person to this SocialGroup, using their username.
     * Example: $group->addPersonByUsername('bob');
     * @param string $username A username.
     */
   function addPersonByUsername($username) {
      $id = $this->world->getUserIdFromUsername($username);
      return $this->addPersonById($id);
   }
   /**
     * Add a Person to this SocialGroup, using their user-ID.
     * Example: $group->addPersonById(12);
     * @param integer $id A user's unique ID#.
     */
   function addPersonById($id) {
      // the moderator cannot join their own group
      if ($id == $this->moderator->id) return;
      // make them a member
      $this->world->db->Execute('INSERT INTO socialgroups_people (group_id,person_id) VALUES (?,?)', array($this->id,$id) );
      // invalidate any now-superfluous requests or invitations
      $this->removeRequestByPersonId($id);
      $this->cancelInviteByPersonId($id);
   }
   /**
     * Remove a Person from this SocialGroup, using their username.
     * Example: $group->removePersonByUsername('bob');
     * @param string $username A username.
     */
   function removePersonByUsername($username) {
      $id = $this->world->getUserIdFromUsername($username);
      return $this->removePersonById($id);
   }
   /**
     * Remove a Person from this SocialGroup, using their user-ID.
     * Example: $group->removePersonById(12);
     * @param integer $id A user's unique ID#.
     */
   function removePersonById($id) {
      // remove their membership
      $this->world->db->Execute('DELETE FROM socialgroups_people WHERE group_id=? AND person_id=?', array($this->id,$id) );
      // invalidate any now-superfluous requests or invitations
      $this->removeRequestByPersonId($id);
      $this->cancelInviteByPersonId($id);
   }
   /**
     * Query whether a Person is in this SocialGroup, using their username.
     * Example: $group->isInGroupByUsername('bob');
     * @param string $username A username.
     * @return boolean True or False, indicating whether they're in this group.
     */
   function isInGroupByUsername($name) {
      $id = $this->world->getUserIdFromUsername($name);
      return $this->isInGroupById($id);
   }
   /**
     * Query whether a Person is in this SocialGroup, using their user-ID.
     * Example: $group->isInGroupById(12);
     * @param integer $id A user's unique ID#.
     * @return boolean True or False, indicating whether they're in this group.
     */
   function isInGroupById($id) {
      // are they moderator? not likely, but a very simple test
      if ($this->moderator->id == $id) return true;
      // are they a member?
      $member = $this->world->db->Execute('SELECT * FROM socialgroups_people WHERE group_id=? AND person_id=?', array($this->id,$id) );
      if (!$member->EOF) return true;
      // got here? I guess not.
      return false;
   }
   /**
     * Count how many members are in this SocialGroup.
     * print "There are " . $group->countMembers() . " members";
     * @return integer The number of members.
     */
   function countMembers() {
      $count = $this->world->db->Execute('SELECT count(*) AS cnt FROM socialgroups_people WHERE group_id=?', array($this->id) )->fields['cnt'];
      return intval($count);
   }

   /////
   ///// adding and removing join requests
   /////
   /**
     * Fetch a list of Person objects, them being people who have requested to join this group.
     */
   function getJoinRequests() {
      $people = $this->world->db->Execute('SELECT g.person_id AS id FROM socialgroups_joinrequests g, people p WHERE g.group_id=? AND p.id=g.person_id ORDER BY p.realname', array($this->id) )->getRows();
      $people = array_map( create_function('$a','return $a["id"];') , $people );
      $people = array_map( array($this->world,'getPersonById') , $people );
      return $people;
   }
   /**
     * Add a request for a Person to join this SocialGroup, using their user-ID.
     * Example: $group->addRequestByPersonId(12);
     * @param integer $id A user's unique ID#.
     * @param boolean $notify True to send a notification to the moderator; False not to notify them. Default is false.
     */
   function addRequestByPersonId($id,$notify=false) {
      // create their request
      $this->world->db->Execute('INSERT INTO socialgroups_joinrequests (group_id,person_id) VALUES (?,?)', array($this->id,$id) );
      // email a notice
      if ($notify) {
        $requestor  = $this->world->getPersonById($id);
        $moderator  = $this->world->getPersonById($this->moderator->id);
        if (!$requestor or !$moderator or !$moderator->email) return;
        $from     = sprintf("From: %s <%s>", $requestor->realname, $requestor->email );
        $to       = sprintf("%s <%s>", $moderator->realname, $moderator->email );
        $subject  = sprintf("[%s] Request from %s to join group %s", $this->world->config['title'], $requestor->username, $this->title );
        $message  = sprintf("%s (%s) requests approval to join your group:\n   %s\n\n", $requestor->realname, $requestor->username, $title->title );
        mail($to,$subject,$message,$from);
      }
   }
   /**
     * Cancel a request for a Person to join this SocialGroup, using their user-ID.
     * Example: $group->removeRequestByPersonId(12);
     * @param integer $id A user's unique ID#.
     */
   function removeRequestByPersonId($id) {
      $this->world->db->Execute('DELETE FROM socialgroups_joinrequests WHERE group_id=? AND person_id=?', array($this->id,$id) );
   }
   /**
     * Count how many join-requests there are for this SocialGroup.
     * print "There are " . $group->countJoinRequests() . " requests";
     * @return integer The number of outstanding join requests.
     */
   function countJoinRequests() {
      $count = $this->world->db->Execute('SELECT count(*) AS cnt FROM socialgroups_joinrequests WHERE group_id=?', array($this->id) )->fields['cnt'];
      return intval($count);
   }

   /////
   ///// adding and removing join invitations
   /////
   /**
     * Fetch a list of Person objects, them being people with open invitations to join this SocialGroup
     */
   function getInvitations() {
      $people = $this->world->db->Execute('SELECT g.person_id AS id FROM socialgroups_invites g, people p WHERE g.group_id=? AND p.id=g.person_id ORDER BY p.realname', array($this->id) )->getRows();
      $people = array_map( create_function('$a','return $a["id"];') , $people );
      $people = array_map( array($this->world,'getPersonById') , $people );
      return $people;
   }
   /**
     * Invite someone to join the SocialGroup. This pre-approves them so they may join without later approval,
     * and optionally notifies the person that they're invited.
     * @param integer $id The person's unique ID#
     * @param boolean $notify True to send a notification to the Person; False not to notify them. Default is false.
     */
   /**
     * Find out whether a given person (by their unique ID#) has an invitation to this SocialGroup
     * @param integer $id The person's unique ID#
     * @return boolean True/false indicating whether this person has an outstanding invitation.
     */
   function isInvitedByPersonId($id) {
      $ok = $this->world->db->Execute('SELECT count(*) as cnt FROM socialgroups_invites WHERE group_id=? AND person_id=?', array($this->id,$id) );
      $ok = intval($ok->fields['cnt']);
      return (boolean) $ok;
   }
   /**
     * Count how many outstanding invitations there are for this SocialGroup.
     * print "There are " . $group->countInvites() . " unanswered invitations";
     * @return integer The number of outstanding join requests.
     */
   function countInvitations() {
      $count = $this->world->db->Execute('SELECT count(*) AS cnt FROM socialgroups_invites WHERE group_id=?', array($this->id) )->fields['cnt'];
      return intval($count);
   }

   /////
   ///// fetching and setting group-permissions granted to Layers and Projects
   /////
   /**
     * Fetch a list of all Layers which have a sharing status to this SocialGroup
     */
   function listSharedLayers() {
      $layers = $this->world->db->Execute('SELECT layer_id FROM layersharing_socialgroups WHERE group_id=? AND permission>?', array($this->id,AccessLevels::NONE) )->getRows();
      $layers = array_map( create_function('$a','return $a["layer_id"];') , $layers );
      $layers = array_map( array($this->world,'getLayerById') , $layers );
      return $layers;
   }
   /**
     * Fetch a list of all Maps/Projects which have a sharing status to this SocialGroup
     */
   function listSharedProjects() {
      $projects = $this->world->db->Execute('SELECT project_id FROM projectsharing_socialgroups WHERE group_id=? AND permission>?', array($this->id,AccessLevels::NONE) )->getRows();
      $projects = array_map( create_function('$a','return $a["project_id"];') , $projects );
      $projects = array_map( array($this->world,'getProjectById') , $projects );
      return $projects;
   }
   /**
     * Set the permission granted to a Layer with respect to this SocialGroup.
     * @param integer $id The Layer's unique ID#.
     * @param integer $level A permission level from the AccessLevels::* defines.
     */
   function setLayerPermission($id,$level) {
      // since the table has foreign key constraints and unique indexes, we don't have to verify that the Layer exists
      $this->world->db->Execute('UPDATE layersharing_socialgroups SET permission=? WHERE layer_id=? AND group_id=?', array($level,$id,$this->id) );
      $this->world->db->Execute('INSERT INTO layersharing_socialgroups (group_id,layer_id,permission) VALUES (?,?,?)', array($this->id,$id,$level) );
   }
   /**
     * Fetch the permission for a Layer with respect to a given SocialGroup.
     * @param integer $id The Layer's unique ID#.
     * @return integer A permission level from the AccessLevels::* defines.
     */
   function getLayerPermission($id) {
      $permission = $this->world->db->Execute('SELECT permission FROM layersharing_socialgroups WHERE layer_id=? AND group_id=?', array($id,$this->id) )->fields['permission'];
      return intval($permission);
   }
   /**
     * Set the permission granted to a Project with respect to this SocialGroup.
     * @param integer $id The Project's unique ID#.
     * @param integer $level A permission level from the AccessLevels::* defines.
     */
   function setProjectPermission($id,$level) {
      // since the table has foreign key constraints and unique indexes, we don't have to verify that the Project exists
      $this->world->db->Execute('UPDATE projectsharing_socialgroups SET permission=? WHERE project_id=? AND group_id=?', array($level,$id,$this->id) );
      $this->world->db->Execute('INSERT INTO projectsharing_socialgroups (group_id,project_id,permission) VALUES (?,?,?)', array($this->id,$id,$level) );
   }
   /**
     * Fetch the permission for a Layer with respect to a given SocialGroup.
     * @param integer $id The Project's unique ID#.
     * @return integer A permission level from the AccessLevels::* defines.
     */
   function getProjectPermission($id) {
      $permission = $this->world->db->Execute('SELECT permission FROM projectsharing_socialgroups WHERE project_id=? AND group_id=?', array($id,$this->id) )->fields['permission'];
      return intval($permission);
   }
   
	function notify($toUser, $fromUser, $type){
		$toUser  = $this->world->getPersonById($toUser);
		$fromUser  = $this->world->getPersonById($fromUser);
		$from     = sprintf("From: %s <%s>\r\n", $fromUser->realname, $fromUser->email );
		$from .= "Content-type: text/html\r\n"; 
		$to       = sprintf("%s <%s>", $toUser->realname, $toUser->email );
		$email = new SLSmarty();
		$message = '';
		
		switch($type){
		case 'invite':
			$subject  = sprintf("[Group: %s] Invitation to group", $this->title );
			$message .= sprintf("You have been invited to join the group %s\n", $this->title);
			$message .= sprintf("<a style=\"text-decoration:none;\" href=\"%s%s?do=group.action&action=acceptinvite&group=%s\">Accept</a> - <a style=\"text-decoration:none;\"  href=\"%s%s?do=group.action&action=denyinvite&group=%s\">Deny</a>", BASEURL, $this->id, $devpath, $this->id);
			break;
		case 'uninvite':
			$subject  = sprintf("[Group: %s] Invitation Canceled", $this->title );
			$message .= sprintf("Your invite to join the group %s has been canceled.", $this->title);
			break;
		case 'acceptinvite':
			$subject  = sprintf("[Group: %s] %s's Invitation Accepted", $this->title, $fromUser->realname);
			$message .= sprintf("%s has accepted your invitation to %s.", $fromUser->realname, $this->title);
			break;
		case 'denyinvite':
			$subject  = sprintf("[Group: %s] %s's Invitation Denied", $this->title, $fromUser->realname);
			$message .= sprintf("%s has denied your invitation to %s.", $fromUser->realname, $this->title);
			break;
		case 'request':
			$subject  = sprintf("[Group: %s] %s has requested to join", $this->title, $fromUser->realname);
			$message .= sprintf("%s has requested to join the group %s.<br/><br/>", $fromUser->realname, $this->title);
			$message .= sprintf("<a style=\"text-decoration:none;\" href=\"https://www.cartograph.com/%s?do=group.action&action=acceptrequest&user=%s&group=%s\">Accept</a> - <a style=\"text-decoration:none;\"  href=\"https://www.cartograph.com/%s?do=group.action&action=denyrequest&user=%s&group=%s\">Deny</a>", $devpath, $fromUser->id, $this->id, $devpath, $fromUser->id, $this->id);
			break;
		case 'unrequest':
			$subject  = sprintf("[Group: %s] has canceled the request join", $this->title );
			$message .= sprintf("%s has canceled the request to join %s.", $fromUser->realname, $this->title);
			break;
		case 'acceptrequest':
			$subject  = sprintf("[Group: %s] Request to join accepted", $this->title );
			$message .= sprintf("Your request to join %s has been accepted.", $this->title);
			break;
		case 'denyrequest':
			$subject  = sprintf("[Group: %s] Request denied", $this->title );
			$message .= sprintf("Your request to join %s has been denied.", $this->title);
			break;
		case 'kick':
			$subject  = sprintf("[Group: %s] Removed from group", $this->title );
			$message .= sprintf("You have been removed from the group %s.", $this->title);
			break;
		case 'join':
			$subject  = sprintf("[Group: %s] %s Joined", $this->title, $fromUser->realname);
			$message .= sprintf("%s joined the group %s.", $fromUser->realname, $this->title);
			break;
		}
		
		$simpleMessage = SimpleMessage::NewMessage($subject, $fromUser->realname, $fromUser->email,$message);
		$simpleMessage['group'] = $this;
		$simpleMessage['logo'] = BASEURL.'logo.php';
		
		SimpleMail::SendTemplatedMessage($toUser->email, $simpleMessage,BASEURL.'context/dmi/templates/group/email.tpl');
		
		//mail($to,$subject,$email->fetch('group/email.tpl'),$from);
	}
	
	function inviteById($id,$notify=false) {
		if(!$this->isNothing($id)) return;
		$this->world->db->Execute('INSERT INTO socialgroups_invites (group_id,person_id) VALUES (?,?)', array($this->id,$id) );
		$this->world->db->Execute('DELETE FROM socialgroups_denied WHERE group_id=? AND person_id=?', array($this->id,$id) );
		if($notify) $this->notify($id, $this->moderator->id, 'invite');
	}
	function uninviteById($id,$notify=false) {
		if(!$this->isInvited($id)) return;
		$this->world->db->Execute('DELETE FROM socialgroups_invites WHERE group_id=? AND person_id=?', array($this->id,$id) );
		if($notify) $this->notify($id, $this->moderator->id, 'uninvite');
	}
	function acceptInviteById($id,$notify=false) {
		if(!$this->isInvited($id)) return;
		$this->addPersonById($id);
		$this->uninviteById($id,false);
		if($notify) $this->notify($this->moderator->id, $id, 'acceptinvite');
	}
	function denyInviteById($id,$notify=false) {
		if(!$this->isInvited($id)) return;
		$this->uninviteById($id,false);
		if($notify) $this->notify($this->moderator->id, $id, 'denyinvite');
	}
	function acceptRequestById($id,$notify=false) {
		if(!$this->isRequesting($id) && !$this->isDenied($id)) return;
		$this->world->db->Execute('DELETE FROM socialgroups_denied WHERE group_id=? AND person_id=?', array($this->id,$id) );
		$this->addPersonById($id);
		$this->unrequestById($id);
		if($notify) $this->notify($id, $this->moderator->id, 'acceptrequest');
	}
	function denyRequestById($id,$notify=false) {
		if(!$this->isRequesting($id)) return;
		$this->world->db->Execute('INSERT INTO socialgroups_denied (group_id,person_id) VALUES (?,?)', array($this->id,$id) );
		$this->unrequestById($id);
		if($notify) $this->notify($id, $this->moderator->id, 'denyrequest');
	}
	function joinById($id,$notify=false) {
		if(!$this->isNothing($id)) return;
		$this->addPersonById($id);
		if($notify) $this->notify($this->moderator->id, $id, 'join');
	}
	function requestById($id,$notify=false) {
		if(!$this->isNothing($id)) return;
		$this->world->db->Execute('INSERT INTO socialgroups_joinrequests (group_id,person_id) VALUES (?,?)', array($this->id,$id) );
		if($notify) $this->notify($this->moderator->id, $id, 'request');
	}
	function unrequestById($id,$notify=false) {
		if(!$this->isRequesting($id)) return;
		$this->world->db->Execute('DELETE FROM socialgroups_joinrequests WHERE group_id=? AND person_id=?', array($this->id,$id) );
		if($notify) $this->notify($this->moderator->id, $id, 'unrequest');
	}
	function kickById($id,$notify=false) {
		if(!$this->isAccepted($id)) return;
		$this->removePersonById($id);
		if($notify) $this->notify($id, $this->moderator->id, 'kick');
	}
	function leaveById($id,$notify=false) {
		if(!$this->isAccepted($id)) return;
		$this->removePersonById($id);
	}
	
	
	function isAccepted($id){
		$result = $this->world->db->Execute('SELECT id FROM socialgroups_people WHERE group_id=? AND person_id=?', array($this->id,$id))->FetchRow();
		if($result["id"]) return true;
		return false;
	}
	function isRequesting($id){
		$result = $this->world->db->Execute('SELECT gid FROM socialgroups_joinrequests WHERE group_id=? AND person_id=?', array($this->id,$id))->FetchRow();
		if($result["gid"]) return true;
		return false;
	}
	function isInvited($id){
		$result = $this->world->db->Execute('SELECT id FROM socialgroups_invites WHERE group_id=? AND person_id=?', array($this->id,$id))->FetchRow();
		if($result["id"]) return true;
		return false;
	}
	function isDenied($id){
		$result = $this->world->db->Execute('SELECT id FROM socialgroups_denied WHERE group_id=? AND person_id=?', array($this->id,$id))->FetchRow();
		if($result["id"]) return true;
		return false;
	}
	function isNothing($id){
		if($this->isAccepted($id) || $this->isRequesting($id) || $this->isInvited($id) || $this->isDenied($id)) return false;
		return true;
	}
	
	function getStatus($uid=null){
		if($uid === null) $uid = $user->id;
		$gid = $this->id;
		$query = "SELECT actor AS status FROM groups WHERE id = ?";
		return $this->world->db->GetOne($query, Array($uid,$uid,$gid,$uid,$gid,$uid,$gid,$uid,$gid,$gid));
	}
	
	function invitePersonById($id,$notify=false) {
		$this->world->db->Execute('INSERT INTO groups_members (group_id,person_id) VALUES (?,?)', array($this->id,$id) );
	}
	function cancelInviteByPersonId($id,$notify=false) {
		$this->world->db->Execute('DELETE FROM socialgroups_invites WHERE group_id=? AND person_id=?', array($this->id,$id) );
	}
}?>