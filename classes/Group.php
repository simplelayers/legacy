<?php
use model\CRUD;
use model\reporting\Notifications;
use mail\SimpleMail;
use mail\SimpleMessage;
use utils\ParamUtil;
/*
 * Created on Sep 14, 2009
 *
 */
class Group extends CRUD {
	
	protected $arrayOfObjectsRetrievedRowData = null;
	
	const ACTOR_NONE = 0;
	const ACTOR_MEMBER = 1;
	const ACTOR_PENDING_INVITE = 2;
	const ACTOR_PENDING_REQUEST = 3;
	const ACTOR_DENIED = 4;
	const ACTOR_MODERATOR = 5;
	
	private $_moderator = null;
	private $_moderatorUser = null;	
	
	function __construct(&$world, $id) {
		$this->world = $world;
		$this->table = "groups";
		$this->idField = 'id';
		$this->objectId = $id;
		#$this->isReadyOnly = false;
		
		$this->arrayOfObjectsRetrievedRowData = $this->getAllFieldsAsArray();
		$this->mod = $world->getPersonById($this->getMod());
		
	}		
	public function Update($updates) {
		parent::Update($updates);
		$this->arrayOfObjectsRetrievedRowData = $this->getAllFieldsAsArray();
	}
	
	
	public function __get($name) {
	    switch($name) {
	        case 'moderator':
	        case "mod":
	            $modId = $this->getMod();
	            
	            return Person::Get($modId);
	            break;
	    }
		if(isset($this->arrayOfObjectsRetrievedRowData[$name])) return $this->arrayOfObjectsRetrievedRowData[$name];
	}
	
	public function __set($target,$val) {
		
		$query = "UPDATE groups set $target=? where id=".$this->objectId;
		$this->world->db->Execute($query,array($val));
		
		
	}
	
	function notify($moderator, $actor, $type){
	
	    
	   // $from     = sprintf("From: %s <%s>\r\n", $fromUser->realname, $fromUser->email );
	   // $from .= "Content-type: text/html\r\n";
	    //$to       = sprintf("%s <%s>", $toUser->realname, $toUser->email );
	    $email = new SLSmarty();
	    $message = '';
	      $subject = "";
	    switch($type){
	        case 'invite':
	            $toUser  = $this->world->getPersonById($actor);
	            $fromUser  = $this->world->getPersonById($moderator);
	            $subject  = sprintf("[Group: %s] Invitation to group", $this->title );
	            $message .= sprintf("You have been invited to join the group %s\n", $this->title);
	            $acceptURL = BASEURL.'mail_action/groups/group:'.$this->id.'/action:acceptinvite/sender:'.$actor;
	            $declineURL = BASEURL.'mail_action/groups/group:'.$this->id.'/action:denyinvite/sender:'.$actor;
	            $message .= "<a style=\"text-decoration:none;\" href=\"$acceptURL\">Accept</a> - <a style=\"text-decoration:none;\"  href=\"$declineURL\">Decline</a>";
	            break;
	        case 'uninvite':
	            $toUser  = $this->world->getPersonById($actor);
	            $fromUser  = $this->world->getPersonById($moderator);
	            $subject  = sprintf("[Group: %s] Invitation Canceled", $this->title );
	            $message .= sprintf("Your invitation to join the group %s has been canceled.", $this->title);
	            break;
	        case 'acceptinvite':
	            $toUser   = $this->world->getPersonById($moderator);
	            $fromUser = $this->world->getPersonById($actor);
	            $subject  = sprintf("[Group: %s] %s's Invitation Accepted", $this->title, $fromUser->realname);
	            $message .= sprintf("%s has accepted your invitation to group %s.", $fromUser->realname, $this->title);
	            break;
	        case 'denyinvite':
	            $toUser   = $this->world->getPersonById($moderator);
	            $fromUser = $this->world->getPersonById($actor);
	            $subject  = sprintf("[Group: %s] %s's Invitation Denied", $this->title, $fromUser->realname);
	            $message.= sprintf("%s has denied your invitation to group %s.", $fromUser->realname, $this->title);
	            break;
	        case 'request':
	            $toUser   = $this->world->getPersonById($moderator);
	            $fromUser = $this->world->getPersonById($actor);
	            $subject  = sprintf("[Group: %s] %s has requested to join", $this->title, $fromUser->realname);
	            $message .= sprintf("%s has requested to join the group %s.<br/><br/>", $fromUser->realname, $this->title);
	            $acceptURL = BASEURL."mail_action/groups/group:{$this->id}/action:acceptrequest/sender:".$fromUser->id;
	            $declineURL = BASEURL."mail_action/groups/group:{$this->id}/action:denyrequest/sender:".$fromUser->id;	             
	            $message.= "<a style=\"text-decoration:none;\" href=\"$acceptURL\">Accept</a> - <a style=\"text-decoration:none;\"  href=\"$declineURL\">Decline</a>";
	            break;
	        case 'unrequest':
	            $toUser   = $this->world->getPersonById($moderator);
	            $fromUser = $this->world->getPersonById($actor);
	            $subject  = sprintf("[Group: %s] Cancelled join request", $this->title );
	            $message .= sprintf("%s has canceled the request to join %s.", $fromUser->realname, $this->title);
	            break;
	        case 'acceptrequest':
	            $toUser   = $this->world->getPersonById($actor);
	            $fromUser = $this->world->getPersonById($moderator);
	            $subject  = sprintf("[Group: %s] Join request accepted", $this->title );
	            $message .= sprintf("Your request to join %s has been accepted.", $this->title);
	            break;
	        case 'denyrequest':
	            $toUser   = $this->world->getPersonById($actor);
	            $fromUser = $this->world->getPersonById($moderator);
	            $subject  = sprintf("[Group: %s] Join request declined", $this->title );
	            $message .= sprintf("Your request to join %s has been declined.", $this->title);
	            break;
	        case 'kick':
	            $toUser   = $this->world->getPersonById($actor);
	            $fromUser = $this->world->getPersonById($moderator);
	            $subject  = sprintf("[Group: %s] Removed from group", $this->title );
	            $message .= sprintf("You have been removed from the group %s.", $this->title);
	            break;
	        case 'join':
	            
	            $toUser   = $this->world->getPersonById($moderator);
	            $fromUser = $this->world->getPersonById($actor);
	            $subject  = sprintf("[Group: %s] %s Joined", $this->title, $fromUser->realname);
	            $message .= sprintf("%s joined the group %s.", $fromUser->realname, $this->title);
	            break;
	        case 'leave':
	            $toUser   = $this->world->getPersonById($moderator);
	            $fromUser = $this->world->getPersonById($actor);
	            $subject  = sprintf("[Group: %s] %s Left", $this->title, $fromUser->realname);
                $message .= sprintf("%s left the group %s.", $fromUser->realname, $this->title);
                break;
	    }
	     $simpleMessage = SimpleMessage::NewMessage($subject, $fromUser->realname, $fromUser->email,$message);
	    $simpleMessage['group'] = $this;
	    $simpleMessage['logo'] = BASEURL.'logo.php';
	    
	    SimpleMail::SendTemplatedMessage($toUser->email, $simpleMessage,BASEDIR.'contexts/dmi/templates/group/email.tpl');
	
	    //mail($to,$subject,$email->fetch('group/email.tpl'),$from);
	}
	
	
	
	public function getMembers($filterActor=true,$idsOnly=false){
		 $actor = ($filterActor) ? " AND actor=1" : "";
		$query = "SELECT person_id FROM groups_members WHERE group_id=? $actor";
		
		$results = $this->world->db->Execute($query, Array($this->objectId));
		if(!$idsOnly) {
                    $people = array_map( function ($a){ return $a["person_id"];} , $results->getRows() );
		    $people = array_map( array($this->world,'getPersonById') , $people );
		    return $people;
		} else {
		    $personIds = ParamUtil::GetSubValues($results->getRows(),'person_id');
		    return $personIds;		    	   
		}
		
		
	}
	
	public function isMember($memberId) {
		$query = 'Select count(*) as numMatches FROM groups where group_id=? AND person_id=?'; 
		$numMatches = $this->world->db->Execute($query,array($this->id, $memberId));
		return(($numMatches >0));
	}
	
	function createUpdateActor($actor, $id){
		$result = $this->world->db->Execute('SELECT id FROM groups_members WHERE group_id=? AND person_id=?', array($this->id,$id))->FetchRow();
		if(!$result){
			$this->world->db->Execute('INSERT INTO groups_members (group_id,person_id,actor) VALUES (?,?,?)', array($this->id,$id,$actor));
		} elseif($result["id"]){
			$this->world->db->Execute('UPDATE groups_members SET actor=? WHERE group_id=? AND person_id=?', array($actor,$this->id,$id));
		}
	}
	function deleteById($id){
		$this->world->db->Execute('DELETE FROM groups_members WHERE group_id=? AND person_id=?', array($this->id,$id) );
	}
	function inviteById($id,$notify=false) {
		if($this->getStatus($id) != self::ACTOR_NONE) return;
		$this->createUpdateActor(self::ACTOR_PENDING_INVITE, $id);
		if($notify) {
		    $this->world->getPersonById($id)->notify($this->mod->id, "invited you to the group:", $this->title, $this->id, BASEURL."/?do=group.info&groupId=".$this->id, 9);
		    $this->notify($this->mod->id,$id,'invite');
		}		
	}
	function uninviteById($id,$notify=false) {
		if($this->getStatus($id) != self::ACTOR_PENDING_INVITE) return;
		$this->deleteById($id);
		if($notify) {
		    $this->notify($this->mod->id,$id,'uninvite');
		}
		//TODO Make this delete notifications
	}
	function acceptInviteById($id,$notify=false) {
		if($this->getStatus($id) !=self::ACTOR_PENDING_INVITE) return;
		$this->createUpdateActor(self::ACTOR_MEMBER, $id);		
		if($notify) {
		    $this->mod->notify($id, "accepted your invite to the group:", $this->title, $this->id, BASEURL."./group/info/groupId:".$this->id, 9);
		    $this->notify($id,$this->mod->id,'acceptinvite');
		}
	}
	function denyInviteById($id,$notify=false) {
		if($this->getStatus($id) != self::ACTOR_PENDING_INVITE) return;
		$this->deleteById($id);
		if($notify) {
		    $this->mod->notify($id, "denied your invite to the group:", $this->title, $this->id, BASEURL."./group/info/groupId:".$this->id, 9);
		    $this->notify($id,$this->mod->id,'denyinvite');
		}
	}
	function acceptRequestById($id,$notify=false) {
		if($this->getStatus($id) != self::ACTOR_PENDING_REQUEST && $this->getStatus($id) != self::ACTOR_DENIED) return;
		$this->createUpdateActor(self::ACTOR_MEMBER, $id);
		if($notify){
		    $this->world->getPersonById($id)->notify($this->mod->id, "accepted your request to join the group:", $this->title, $this->id, BASEURL."./group/info/groupId:".$this->id, 9);
		    $this->notify($id,$this->mod->id,'acceptrequest');
		}
	}
	function denyRequestById($id,$notify=false) {
	  	if($this->getStatus($id) != self::ACTOR_PENDING_REQUEST) return;
		$this->createUpdateActor(self::ACTOR_DENIED, $id);
		if($notify) {
		    $this->world->getPersonById($id)->notify($this->mod->id, "denied your request to join the group:", $this->title, $this->id, BASEURL."./group/info/groupId:".$this->id, 9);
		    $this->notify($id,$this->mod->id,'denyrequest');		   
		}
	}
	function joinById($id,$notify=false) {
		$this->createUpdateActor(self::ACTOR_MEMBER, $id);
		if($notify){
		    
		    $this->mod->notify($id, "join the group:", $this->title, $this->id, BASEURL."./group/info/groupId:".$this->id, 9);
		    $this->notify($this->mod->id,$id,'join');
		    
		}
		
	}
	function requestById($id,$notify=false) {
		if($this->getStatus($id) != 0) return;
		$this->createUpdateActor(self::ACTOR_PENDING_REQUEST, $id);
		if($notify) {
		    $this->mod->notify($id, "requested to join the group:", $this->title, $this->id, BASEURL."./group/info/groupId:".$this->id, 9);
		    $this->notify($this->mod->id,$id,'request');
		}
	}
	function unrequestById($id,$notify=false) {
		if($this->getStatus($id) != self::ACTOR_PENDING_REQUEST) return;
		$this->deleteById($id);
		if($notify) {
		    $this->notify($this->mod->id,$id,'unrequest');		    
		}
		//TODO Make this delete request notification
	}
	function kickById($id,$notify=false) {
		if($this->getStatus($id) != self::ACTOR_MEMBER) return;
		$this->deleteById($id);
		if($notify) {
		    $this->notify($this->mod->id,$id,'kick');
		}
		//This is awkward. A notification could be created.
	}
	function leaveById($id,$notify=false) {
		if($this->getStatus($id) != self::ACTOR_MEMBER) return;
		$this->deleteById($id);
		if($notify) {
		    $this->notify($this->mod->id,$id,'leave');
		}
	}
	function modById($id,$notify=false) {
		
		if($this->getStatus($id) != 1) return;
		if($this->moderator->id == $id) return;
		$this->createUpdateActor(self::ACTOR_MEMBER, $this->moderator->id);
		$this->createUpdateActor(self::ACTOR_MODERATOR, $id);
		$this->world->getPersonById($id)->notify($this->mod->id, "made you moderator of the group:", $this->title, $this->id, BASEURL."./group/info/groupId:".$this->id, 9);
	}
	
	
	
	function getMod(){
	    if(!is_null($this->_moderator)) return $this->_moderator;
		$query = "SELECT person_id FROM groups_members WHERE group_id=? AND actor=?";
		
		$this->_moderator = $this->world->db->GetOne($query, Array($this->objectId,self::ACTOR_MODERATOR));
	}
	
	function isModerator($userId) {
	    return ($this->getMod()==$userId);
	}
	
	function getStatus($uid=null){
	    if($uid === null) $uid = $user->id;
	    $result = $this->world->db->GetOne('SELECT actor FROM groups_members WHERE group_id=? AND person_id=?', Array($this->objectId,$uid));
	    
	    if($result===false) return 0;
	    return $result;		
	}
	function setSeat($id, $seat){
	    if($seat== self::ACTOR_MODERATOR) $this->_moderator = null;
		return $this->world->db->Execute('UPDATE groups_members SET seat=? WHERE group_id=? AND person_id=?', array($seat,$this->id,$id));
	}
public function newDiscussion($user, $name, $post){
	
	$new = $this->world->db->GetOne("INSERT INTO discussions (group_id, owner, lastpostby, name ) VALUES ( ?, ?, ? ,?) RETURNING id;", array($this->id, $user->id, $user->id, htmlentities($name)));
		$this->world->db->Execute("INSERT INTO discussions_posts (text, owner, dis_id ) VALUES ( ?, ? ,?);", array(nl2br(htmlentities($post)), $user->id, $new));
		foreach( $this->getMembers(true) as $tosend){
			//$tosend->notify($user->id, "stared a new discussion titled:", $name, $new, BASEURL."./do=group.discussion.view&id=".$this->id."&view=".$new, 6);
		}
		return $new;
	}
	public function getDiscussion($user, $id=false){
		$retrive = Array($user->id, $this->id);
		if($id){ $retrive[] = $id; 
			$this->world->db->Execute("UPDATE discussions SET views = views+1 WHERE id = ?;", array($id));}
		$query = "SELECT *, (SELECT realname FROM people WHERE id = d.owner) AS owner_name, (SELECT realname FROM people WHERE id = d.lastpostby) AS lastpostby_name, (SELECT count(id)-1 FROM discussions_posts WHERE dis_id = d.id) AS replies, (SELECT last_viewed FROM discussions_views WHERE dis_id = d.id AND owner = ?) AS last_viewed FROM discussions AS d WHERE group_id=?".(($id) ? " AND id=?" : "")." ORDER BY lastposttime DESC";
		$results = $this->world->db->Execute($query, $retrive);
		if($id) $this->world->db->Execute("SELECT upsert_discussions_views(?, ?)", Array($user->id, $id));
		return $results->getRows();
	}
	public function newReply($user, $dis, $parent, $post){
		$new = $this->world->db->Execute("INSERT INTO discussions_posts (id, text, owner, dis_id, parent) VALUES (DEFAULT, ?, ? ,?, ?) RETURNING id;", array(nl2br(htmlentities($post)), $user->id, $dis, $parent))->fields["id"];
		$this->world->db->Execute("UPDATE discussions SET lastposttime = now(), lastpostby = ? WHERE id = ?;", array($user->id, $dis));
		$results = $this->world->db->Execute("WITH RECURSIVE parentTree(id, parent, owner) AS(
				SELECT id, parent, owner FROM discussions_posts WHERE id = ?
			  UNION ALL
				SELECT 
				t.id, 
				t.parent, 
				t.owner
				FROM discussions_posts t
				JOIN parentTree rt ON rt.parent = t.id
			)
			SELECT owner FROM parentTree GROUP BY owner", array($parent))->getRows();
		$discussion = $this->getDiscussion($user, $dis);
		foreach($results as $result){
			$this->world->getPersonById($result["owner"])->notify($user->id, "replied to discussion titled:", $discussion[0]["name"], $this->id, BASEURL."./do=group.discussion.view&id=".$this->id."&view=".$dis."#".$new, 1);
		}
		return $new;
	}
	public function getReply($dis, $id=false){
		$retrive = Array($dis);
		if($id) $retrive[] = $id;
		$query = "SELECT * FROM discussions_posts AS d WHERE dis_id = ?".(($id) ? " AND id=?" : "")." ORDER BY created";
		$result = $this->world->db->GetRow($query, $retrive);
		return $result;
	}
	private function nestResults($results, $id){
		$return = Array();
		foreach($results as $result){
			if($result["parent"] == $id){
				$result["fromnow"] = timeToHowLongAgo(time()-strtotime($result["created"]));
				$return[$result["id"]] = Array("data"=>$result);
			}
		}
		foreach($return as $key=>&$result){
			$result["children"] = $this->nestResults($results, $key);
		}
		return $return;
	}
	public function getNestedReplies($dis){
		$query = "SELECT * FROM discussions_posts AS d WHERE dis_id = ? ORDER BY created";
		$results = $this->world->db->Execute($query, array($dis));
		$return = $this->nestResults($results, 0);
		return $return;
	}
	public function deleteReply($view,$id){
		$this->world->db->Execute("UPDATE discussions_posts SET text='Comment Removed' WHERE id = ? AND dis_id=?", array($id,$view));
		
	}
	public function deleteDiscussion($id){
		$this->world->db->Execute("DELETE FROM discussions WHERE id = ?", array($id));
		
	}
	
	/** 
	 * @param string $groupName Group's title
	 * @return Group <multitype:, boolean>
	 */
	public static function GetGroupByName($groupName) {
		$db =  System::GetDB(System::DB_ACCOUNT_SU);
		$groupInfo = $db->GetRow('select * from groups where title=?',array($groupName));
		return ($groupInfo) ? System::Get()->getGroupById($groupInfo['id']) : null;
			
	}
	public static function GetGroup($groupId) {
		if(self::GroupExists($groupId))	return new Group(System::Get(),$groupId);
		return false;
		
	}
	public static function GroupExists($groupId) {
		$db =  System::GetDB(System::DB_ACCOUNT_SU);
		$groupInfo = $db->GetRow('select * from groups where id=?',array($groupId));
		return $groupInfo;
		
	}
	public static function DeleteGroup($groupId) {
		
		$db = System::GetDB(System::DB_ACCOUNT_SU);
		$query = "DELETE FROM groups WHERE id=?";
		$result = $db->Execute($query,array($groupId));
		$query = "DELETE FROM groups_members WHERE group_id=?";
		$result = $db->Execute($query,array($groupId));
	
	}
	
	
}
?>