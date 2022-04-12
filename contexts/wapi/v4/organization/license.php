<?php
use utils\ParamUtil;
use model\SeatAssignments;
use model\License;




function _exec() {
	$wapi = System::GetWapi ();
	
	$args = $_REQUEST;
	
	$action = strtolower ( ParamUtil::Get ( $args, 'action' ) );
	$what = strtolower ( ParamUtil::Get ( $args, 'what' ) );
	$format = WAPI::GetFormat ();
	$orgId = ParamUtil::Get ( $_REQUEST, 'orgId' );
	$user = SimpleSession::Get ()->GetUserInfo ();
	$org = is_null ( $orgId ) ? Organization::GetOrgByUserId ( $user ['id'] ) : Organization::GetOrg ( $orgId );
	$licenses = new License();
	
	switch ($action) {
		case WAPI::ACTION_GET:
			
			
			$licence = $licenses->Get($org->id);

			if(is_null($licence)) {
				$licenses->Create(array('orgId'=>$org->id));
			}

			$new_plan  = ParamUtil::Get($args,'new_plan');
			
			$licence = $licenses->Get($org->id,$new_plan);
			
			WAPI::SendSimpleResults ( $licence );
			
			break;
			
		case WAPI::ACTION_SAVE :
			list ( $document ) = ParamUtil::Requires ( $args, 'changeset' );
			
			if(is_null($orgId)) $orgId = $document['data']['orgId'];
			
			$org = Organization::GetOrg($orgId);
			$owner_seat = $document['data']['plan']['data']['owner_seat'];
			$owner = $org->owner->id;
			//$seatAssignments  = new SeatAssignments();
			
			$licenses->UpdateLicense($document);
			//$doc = $seatAssignments->AssignSeat(array('orgId'=>$org->id,'userId'=>$owner,'seatId'=>$owner_seat));				
			
			WAPI::SendSimpleResults(array('message'=>'updates complete'));
			/*
			 * foreach($changes as $change) { $seats->UpdateSeat($change); } WAPI::SendSimpleResults(array('message'=>'updates complete'));
			 */
			break;
	}
}

?>