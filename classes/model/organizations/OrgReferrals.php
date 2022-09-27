<?php
namespace model\organizations;

use model\MongoCRUD;
use utils\ParamUtil;
use model\mail\MailTemplate;
use mail\SimpleMail;

class OrgReferrals
extends MongoCRUD {

	const STATUS_SENT = 'sent';
	const STATUS_ORG_CREATED = 'org_created';
	
	
	public function __construct() {
		$this->collectionName = 'org_referrals';
		parent::__construct();		
		$this->collection->createIndex(array('data.orgId'=>1,'data.referrals.email'=>1));
	}
	
	public function RemoveOrg($orgId) {
		$this->DeleteByCriteria(\Comparisons::ToMongoCriteria('data.orgId',\Comparisons::COMPARE_EQUALS,$orgId));
	}
	
	public function GetReferrals($orgId=null,$referralId=null) {
		
		//if(is_null($orgId) && is_null($referralId)) return array();
		$criteria = array();
		if(!is_null($orgId)) {
			$criteria = \Comparisons::ToMongoCriteria('data.orgId',\Comparisons::COMPARE_EQUALS,$orgId);
			$orgRecord  = $this->FindOneByCriteria($criteria);
			return $orgRecord['data']['referrals'];
		} elseif( !is_null($referralId)) {		
			return $this->GetOrgRecordByRefId($referralId,false);			
		} else {
		    $res = $this->FindByCriteria();
		    $results = array();
		    foreach($res as $r) {
		        foreach($r['data']['referrals'] as $ref) {
		            $results[] = $ref;
		        }
		        
		    }
		    return $results;
		}
		
		if($referralId) {
			foreach($orgRecord['data']['referrals'] as $referral) {
				if($referral['id'] == $referralId) return $referral;
			}
			return $referralId;
		}
		
	}
		

	public function GetOrgRecord($orgId) {
		$criteria = \Comparisons::ToMongoCriteria('data.orgId',\Comparisons::COMPARE_EQUALS,$orgId);
		$orgRecord  = $this->FindOneByCriteria($criteria);
		return $orgRecord;
	}
	
	public function GetOrgRecordByRefId($referralId,$asOrgRecord = true) {
		$orgRecord = $this->FindOneByCriteria(\Comparisons::ToMongoCriteria('data.referrals.id',\Comparisons::COMPARE_EQUALS,$referralId));
		if($asOrgRecord) return $orgRecord;
		foreach($orgRecord['data']['referrals'] as $referralRecord ) {
			
			if($referralId == $referralRecord['id']) return $referralRecord;
		}
	}
	
	public function MakeDocument($orgId, $mergeData = false, $insert = true) {
		$document = array();
		$document['orgId'] = $orgId;
		$document['referrals'] = array();
		$document['numCredits'] = array();
		return parent::MakeDocument($document,$mergeData,$insert);
			
	}
	
	public static function CreateReferral($params) {
		$ref = new OrgReferrals();
		$orgId = ParamUtil::RequiresOne($params,'orgId');
		
		return $ref->AddReferral($orgId,$params);
	}
	
	
	public function AddReferral($orgId,$referral){
		$referral['id'] = self::MakeId();
		$referral = ParamUtil::GetValues($referral,'email','name','organization','title','reason','id');
		$referral['referrer'] = $orgId;
		$referral['referrer_name'] = \Organization::GetOrg($orgId)->name;
		$referral['status'] = self::STATUS_SENT;
		
		
		$orgRecord = $this->GetOrgRecord($orgId);
		$exists = false;
		if($orgRecord) {
			foreach($orgRecord['data']['referrals'] as $extantRef) {
				if(trim(strtolower($extantRef['email']))==trim(strtolower($referral['email']))) {
					//if($extantRef['referrer'] == $referral['referrer']) return $orgRecord;
				}
			}
		}
		
		if(!$orgRecord) {			
			$orgRecord = $this->MakeDocument($orgId);						
		}
		$orgRecord['data']['referrals'][] = $referral;
		
		$this->Update($orgRecord);
		$org = \Organization::GetOrg($orgId);
		
		$this->SendInviteEmail($org,$referral);
		return $referral;
		
	}
	
	public function SendInviteEmail($referrerOrg,$referral) {
		$ini = \System::GetIni();
		
		$referrer_name = $referrerOrg->owner->realname;
		$messageData[SimpleMail::SUBJECT] = 'A recommendation from '.$referrer_name;
		$messageData[SimpleMail::SENDER_EMAIL] =  $ini->invite_email_from;
		$messageData[SimpleMail::SENDER_NAME] = $ini->invite_email_from_name;
		
		
		$recipients = array();
		$cc = array();
		$bcc = array();
		SimpleMail::AddRecipient($referral['email'], $referral['name'],$recipients);
		SimpleMail::AddRecipient($referrerOrg->owner->email, $referrer_name,$cc);
		SimpleMail::AddRecipient($messageData[SimpleMail::SENDER_EMAIL] , $messageData[SimpleMail::SENDER_NAME],$bcc);
		SimpleMail::AddRecipient($ini->sys_email, $ini->sys_email_username,$bcc);
		
		
		$reply_to = $messageData[SimpleMail::SENDER_EMAIL];
				
		$params = ParamUtil::GetValues($referral,'email','name','organization','title','reason');
		$params['referrer_name']  = $referrer_name;
		$params['referrer_org'] = $referrerOrg->name;
		
		$mergeVars=array();
		foreach($params as $key=>$val) {
		    $mergeVars['INVITE_'.strtoupper($key)] = $val;		
		}
		SimpleMail::SendMandrillMessage($recipients, 'Invite', $messageData, $mergeVars,$bcc,$cc);		
		
	}
	
	public function SendThankyouEmail($referrerUser,$referral) {
		$subject = $referral['name'].' has joined SimpleLayers.com';
		$ini = \System::GetIni();
		
		
		$messageData[SimpleMail::SUBJECT] = $referral['name'].' has joined SimpleLayers.com';
		$messageData[SimpleMail::SENDER_EMAIL] =   $ini->invite_email_from;
		$messageData[SimpleMail::SENDER_NAME] = $ini->invite_email_from_name;
		
		$recipients = array();
		$cc = array();
		$bcc = array();
		SimpleMail::AddRecipient( $referrerUser->email, $referrerUser->realname,$recipients);
		SimpleMail::AddRecipient($messageData[SimpleMail::SENDER_EMAIL] , $messageData[SimpleMail::SENDER_NAME],$bcc);
		
		$mergeVars = array();
		$params['CONTACT_NAME'] = $referral['name'];
		foreach($params as $key=>$val) {
			MailTemplate::AddMergeVar($mergeVars, 'INVITE_'.strtoupper($key), $val);
		}
		
		SimpleMail::SendMandrillMessage($recipients,'Invite_Thankyou', $messageData, $mergeVars,$cc,$bcc);		
	}
	
	public function UpdateReferral($orgId,$referral) {
		$criteria = \Comparisons::ToMongoCriteria('data.orgId',\Comparisons::COMPARE_EQUALS,$orgId);
		$orgRecord  = $this->FindOneByCriteria($criteria);
		
		$referral = ParamUtil::GetValues($referral,'name','title','company','email','reason','status','credited','referrer','organization','id');
		$referral['referrer'] = $orgId;
		$numCredits = 0;
		
		foreach($orgRecord['data']['referrals'] as &$ref) {
			
			if($ref['id'] == $referral['id']) {
				foreach($referral as $key=>$val) {
					$ref[$key] = $val;						
				}
			}
			if($ref['status'] == self::STATUS_ORG_CREATED);
			if(isset( $ref['credited'])){
				$numCredits+=($ref['credited']==true) ? 1 :0;
			}
		}
		
		$orgRecord['data']['numCredits'] = $numCredits;
		
		$this->Update($orgRecord);	
	}
	
	public function RemoveReferral($orgId,$referralId) {
		$criteria = \Comparisons::ToMongoCriteria('data.orgId',\Comparisons::COMPARE_EQUALS,$orgId);
		return;
		$orgRecord  = $this->FindOneByCriteria($criteria);
		$numCredits = 0;
		$i = 0;
                
		foreach($orgRecord['data']['referrals'] as $ref) {
			if($ref['id'] == $referralId) {
				unset($orgRecord['data']['referrals'][$i]);
				break;	
			}
			if(isset($ref['credited'])) {
			 if($ref['credited']) $numCredits+=1;
			}
			$i+=1;	
		}
		$orgRecord['data']['referrals'] = array_values($orgRecord['data']['referrals']);
		$orgRecord['data']['numCredits'] = $numCredits;
		$this->Update($orgRecord);
	}
	
	public function HandleChanges($changes) {
			
		foreach($changes as $change) {
			
			if(isset($change['isDeleted'])) {
				$this->RemoveReferral($change['referrer'],$change['id']);
				continue;				
			}
			if(isset($change['isChanged'])) {
				unset($change['isChanged']);
				$this->UpdateReferral($change['referrer'],$change);
			}			
		}		
	}

}

?>