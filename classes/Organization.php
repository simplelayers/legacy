<?php
use model\CRUD;
use model\License;
use model\organizations\OrgMedia;
use utils\ParamUtil;
use model\organizations\OrgReferrals;
use model\SeatAssignments;
use model\logging\Log;
use model\Plans;
use model\Seats;
use enums\AccountTypes;
use model\reporting\Reports;

/*
 * Created on Sep 14, 2009 To change the template for this generated file go to Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class Organization extends CRUD {
	protected $groupId;
	protected $media;
	protected $invites;
	protected $mediaSizeCount;
	protected $nextPayment;
	protected $_plan;
	function __construct(&$world, $id) {
		$this->world = $world;
		$this->table = "organizations";
		$this->idField = 'id';
		$this->objectId = $id;
		$this->arrayOfObjectsRetrivedRowData = $this->getAllFieldsAsArray ();
		
		if ($this->arrayOfObjectsRetrivedRowData == null)
			throw new Exception ( "No such org: $id." );
		$this->groupId = $this->world->db->Execute ( 'SELECT id FROM groups WHERE org_id=?', array (
				$this->objectId 
		) )->fields ['id'];
		
		$this->isReadyOnly = false;
		$this->media = Array ();
		// $this->nextPayment = $this->getNextPayment ();
	}
	public static function GetOrgByUserId($userId,$idOnly=false) {
	    $world = System::Get ();
		
		$id = $world->db->GetOne ( "select groups.org_id as org_id from groups join groups_members on groups.id=groups_members.group_id where org_id is not null and groups_members.person_id='$userId' order by org_id" );	
		if($idOnly) return $id;
		if ($id)
			return new Organization ( $world, $id );
	}
	public static function GetOrgByUserName($userName,$idOnly=false) {
	    $world = System::Get ();
	    $user = $world->getPersonByUsername($userName);
	    if(!$user) return false;
	    return self::GetOrgByUserId($user->id,$idOnly);
	}
	public function Update($updates) {
		parent::Update ( $updates );
		
		$this->arrayOfObjectsRetrivedRowData = $this->getAllFieldsAsArray ();
		
	}
	public function getInvites() {
		if ($this->invites === null)
			$this->invites = $this->world->db->Execute ( "SELECT * FROM organizations_invites WHERE org_id=?", $this->id );
		if ($this->invites)
			$this->invites = $this->invites->GetAssoc ();
		return $this->invites;
	}
	public function addMedia($name, $link, $type, $temp = null) {
		$name = strtolower ( $name );
		// link = strtolower($link);
		$type = strtolower ( $type );
		$size = filesize ( $this->makeLink ( $name ) );
		if ($temp !== null)
			$size = $temp;
		
		if ($this->getMedia ( $name ) !== false)
			$this->world->db->Execute ( "UPDATE organizations_media SET link=?, type=?, diskusage=? WHERE name=? AND org_id=?", array (
					$link,
					$type,
					$size,
					$name,
					$this->id 
			) );
		else
			$this->world->db->Execute ( "INSERT INTO organizations_media (id, org_id, name, link, type, diskusage) VALUES (DEFAULT, ?, ?, ?, ?, ?) RETURNING link;", array (
					$this->id,
					$name,
					$link,
					$type,
					$size 
			) );
		$this->media = Array ();
		return $link;
	}
	public function getMedia($media = null) {
		if (empty ( $this->media ))
			$this->media = $this->world->db->Execute ( "SELECT * FROM organizations_media WHERE org_id=?", $this->id )->GetAssoc();
		
		if ($media !== null) {
			foreach ( $this->media as $row ) {
				;
				if ($row ["name"] == $media)
					return $row;
			}
			return false;
		}
		return $this->media;
	}
	private function mediaSize() {
		#$this->world->db->debug=true;
		
		if ($this->mediaSizeCount === null) {
			$this->mediaSizeCount = $this->world->db->GetOne ( "SELECT sum(diskusage) FROM organizations_media WHERE org_id=?", $this->id );
			$cmd = 'du "/mnt/userimages/org_media/'.$this->id.'/"';
			$cmdRes = shell_exec($cmd);
			$cmdRes = explode("\t",$cmdRes);
			$cmdRes = array_shift($cmdRes);
			$this->mediaSizeCount = floatval($cmdRes);
		}
		return $this->mediaSizeCount;
	}
	public function makeLink($name) {
		
		$media = $this->getMedia ( $name ); $ini = System::GetIni (); $link = $this->makeFileName ( $media ['link'] ); return $link; // eturn "v2.5/org_media/" . $this->id . '/' . $media["link"];
		
	}
	
	public function makeFileName($name) { $ini = System::GetIni (); $fileName = sprintf ( '%s%d/%s', $ini->orgmedia, $this->id, $name ); return $fileName; }
	
	public function getMediaURL($name) {
		$url = BASEURL . '/wapi/organization/media/?do=wapi.organization.media&id=' . $this->id . "&get=$name";
		return $url;
	}
	public function __get($name) {
	    if($name == 'max_layers') {
		$license =  $this->license;
		$layerLimit = $license['data']['max_layers'];
		$layerLimit2 = $license['data']['max_layers'];
		if(is_null($layerLimit)) {
			if($layerLimit2 === '') {
			  return '';
			}
			return +$layerLimit2;
		}
		return $layerLimit;
	    }
		if ($name == "owner")
			return $this->world->getPersonById ( $this->arrayOfObjectsRetrivedRowData [$name] );
		if ($name == "group")
			return $this->world->getGroupById ( $this->groupId );
		if ($name == "diskusage")
			return $this->mediaSize ();
		if ($name == 'license') {
		    $license = new License();
		    return  $license->Get($this->id,false);;
		}
		if ($name == 'plan') {
			if ($this->_plan)
				return $this->_plan;
			$this->_plan = License::GetPlan ( $this->id );
			return $this->_plan;
		}
		if($name == 'plan_info') {
			return License::GetPlan($this->id,true);
		}
		if ($name == 'planid')
			return $this->plan ['id'];
		
		if ($name == "paymentstartdate")
			return date ( 'n/j/Y', strtotime ( $this->arrayOfObjectsRetrivedRowData [$name] ) );
		if ($name == "nextpayment")
			return date ( 'n/j/Y', strtotime ( $this->nextPayment ) );
		if (isset ( $this->arrayOfObjectsRetrivedRowData [$name] ))
			return $this->arrayOfObjectsRetrivedRowData [$name];
		
	}
	
	
	
	
	function notify($address, $code) {
		$fromUser = $this->owner;
		$from = sprintf ( "From: %s <%s>\r\n", $fromUser->realname, $fromUser->email );
		$from .= "Content-type: text/html\r\n";
		$to = sprintf ( "%s", $address );
		$email = new SLSmarty();
		$message = '';
		$devpath = '';
		// $devpath = '~doug/cartograph/';
		
		$subject = sprintf ( "[%s] Invitation to Cartograph", $this->name );
		$message .= sprintf ( "You have been invited to join cartograph by %s as a part of %s.<br/><br/>", $fromUser->realname, $this->name );
		$message .= sprintf ( "You can use the following registration code ot join, or simply use the link below.<br/>" );
		$message .= sprintf ( "%s<br/><br/>", $code );
		$message .= sprintf ( "<a style=\"text-decoration:none;\" href=\"https://www.cartograph.com/%s?do=organization.join&code=%s\">Accept</a>", $devpath, $code );
		
		$email->assign ( 'group', $this );
		$email->assign ( 'subject', $subject );
		$email->assign ( 'message', $message );
		$email->assign ( 'devpath', $devpath );
		mail ( $to, $subject, $email->fetch ( 'group/email.tpl' ), $from );
	}
	public static function GetOrg($orgId) {
	    $sys = System::Get();
	    return new Organization ( $sys, $orgId );
	}
	public static function HandleChanges($changes) {
		$db = System::GetDB ();
		foreach ( $changes as $change ) {
			if (isset ( $change ['isDeleted'] )) {
				$item = self::GetOrg ( $change ['id'] );
				
				if (in_array ( intval ( $item->id ), array (
						1,
						2,
						22 
				) )) {
					Log::Debug ( 'Preventing deletion of core org: ' . $item->id );
					continue;
				}
				Log::Debug ( 'Deleting org: ' . $item->id );
				$group = $item->group;
				if($group) {
					Log::Debug ( 'Deleting org: ' . $item->id );
					$group->org_id = null;
				}
				$orgMedia = new OrgMedia ();
				$orgMedia->RemoveOrg ( $item->id );
				$orgReferrals = new OrgReferrals ();
				$orgReferrals->RemoveOrg ( $item->id );
				$item->Remove ();
				continue;
			}
			if (isset ( $change ['isChanged'] )) {
				unset ( $change ['isChanged'] );
				$item = self::GetOrg ( $change ['id'] );
				$updates = ParamUtil::GetValues ( $change, 'description', 'id', 'name', 'owner', 'short', 'tags' );
				
				$item->Update ( $updates );
			}
		}
	}
	public static function CreateOrg($params) {
		$refId = ParamUtil::Get ( $params, 'refId' );
		$owner = ParamUtil::Get ( $params, 'owner' );
		$account = System::Get ()->getPersonByUsername ( ParamUtil::RequiresOne ( $params, 'account_name' ) );
		
		if (! is_null ( $owner )) {
			$params = ParamUtil::GetRequiredValues ( $params, 'org_name', 'account_name', 'owner', 'planId', 'starts', 'expires' );
			$owner = System::Get ()->getPersonByUsername ( $owner );
			$owner->accounttype = AccountTypes::MAX;
			$owner->expirationdate = null;
		}
		
		if (! $owner) {
			$params = ParamUtil::GetRequiredValues ( $params, 'org_name', 'account_name', 'account_pw', 'email', 'planId', 'starts', 'expires' );
			$owner = System::Get ()->createPerson ( $params ['account_name'], $params ['account_pw'],'Org account' );
			$owner->email = $params['email'];
			$owner->realname = $params['org_name'];
		
		}
		
		if (! $owner)
			throw new Exception ( 'could not find or create an account for account owner' );
		$system = System::Get ();
		
		$org = $system->createOrganization ( $owner->id, $params ['org_name'], $params ['account_name'] );
		
		self::CreateOrgGroup ( $org );
		
		$licenseObj = new License ();
		$params ['orgId'] = $org->id;
		$license = $licenseObj->Create ( $params );
		$seatAssignments = new SeatAssignments ();
		
		$plan = License::GetPlan ( $org->id, true );
		$seatId = $plan ['owner_seat'];
		
		$params = array (
				'orgId' => $org->id,
				'userId' => $owner->id,
				'seatId' => $seatId 
		);
		
		$seatAssignments->AssignSeat ( $params );
	
		
		if ($refId) {
			$referrals = new OrgReferrals ();
			
			$referral = $referrals->GetReferrals ( null, $refId );
			$referral ['status'] = OrgReferrals::STATUS_ORG_CREATED;
			$referral ['orgId'] = $org->id;
			$refOrgId = $referral ['referrer'];
			$referrals->UpdateReferral ( $refOrgId, $referral );
			
			$referrer = Organization::GetOrg ( $refOrgId );
			$buddyList = new BuddyList ( System::Get (), $owner );
			$buddyList->addPersonById ( $referrer->owner->id );
			$buddyList = new BuddyList ( System::Get (), $referrer->owner );
			$buddyList->addPersonById ( $owner->id );
			$referrals->SendThankyouEmail ( $referrer->owner, $referral );
		}
		return $org;
	}
	public static function CreateOrgGroup($org) {
		$system = System::Get ();
		return $system->createGroup ( $org->owner->id, $org->name, $org->name . "'s Discussion Group", $org->id );
	}
	public static function CreateOrgFromGroup($group, $plan = null) {
		$system = System::Get ();
		$sysOwner = System::GetSystemOwner(true);
		
		$seats = new Seats();
		$orgOwner = $seats->GetSeatIdByName ( Seats::SEATNAME_ORGOWNER );
		$unAssigned = $seats->GetSeatIdByName ( Seats::SEATNAME_UNASSIGNED );
		$sysAdmin = $seats->GetSeatIdByName(Seats::SEATNAME_SYSADMIN);
		
		if (is_null ( $plan )) {
			$plans = new Plans ();
			$planId = $plans->GetIdByName ( 'Standard' );
			$plan = Plans::GetPlanById ( $planId );
		}
		
		/* @var $group Group */
		$owner = $group->moderator;
		$org = $system->createOrganization ( $owner->id, $owner->username, $owner->username );
		
		$group->org_id = intval($org->id);
		$org->groupId = $group->id;
		$license = new License ();
		$license->Create ( array (
				'orgId' => $org->id,
				'planId' => $plan['id'],
				'starts' => null,
				'expires' => null 
		) );
		self::AssignSeatsByGroup($org, $group);
		return $org;
	}
	
	public function countLayers() {
	    $report = Reports::Get(Reports::ORG_LAYER_COUNTS,array('orgId'=>$this->id));
	    $data = ParamUtil::ResultsToKeyVal($report, 'org_id', 'layer_count');
	    
	    if(!$data) return 0;
	    $numLayers = +$data[$this->id];
	    
		return $numLayers;
	}
	
	public static function AssignSeatsByGroup($org,$group) {
		$sysOwner = System::GetSystemOwner(true);
		
		$seats = new Seats ();
		$orgOwner = $seats->GetSeatIdByName ( Seats::SEATNAME_ORGOWNER );
		$unAssigned = $seats->GetSeatIdByName ( Seats::SEATNAME_UNASSIGNED );
		$sysAdmin = $seats->GetSeatIdByName(Seats::SEATNAME_SYSADMIN);
		
		$seatAssignments = new SeatAssignments ();
		$seatAssignments->DeleteByCriteria ( array (
				'data.orgId' => '' . $org->id
		) );
		
		$members = $group->getMembers ( false );
		foreach ( $members as $member ) {
			try {
				if ($member->id == $sysOwner) {
					$params = array (
							'orgId' => $org->id,
							'userId' => $member->id,
							'seatId' => $sysAdmin
					);
				} elseif ($member->id == $org->owner->id) {
					$params = array (
							'orgId' => $org->id,
							'userId' => $member->id,
							'seatId' => $orgOwner
					);
					// ar_dump ( $params );
				} else {
					$params = array (
							'orgId' => $org->id,
							'userId' => $member->id,
							'seatId' => $unAssigned
					);
				}
				
				$seatAssignments->AssignSeat ( $params );
			} catch ( Exception $e ) {
				Log::Error($e->getMessage());
			}
		}
	}
	
	public function Remove() {
		
		$this->world->db->Execute ( 'delete from organizations where id=' . $this->id );
	}

	public function CanAddLayers() {
	   $license =  $this->license;
	   
	    $layerLimit = !is_null($license['data']['max_layers']) ? $license['data']['max_layers'] : $license['plan']['data']['max_layers'];
	    
		//$plan = License::GetPlan($this->id,true);
		//$layerLimit = ParamUtil::Get($plan,"max_layers");
		if($layerLimit == "") return true;
		
		return ($this->countLayers()  < $layerLimit) ;
	}
	

	
}
?>
