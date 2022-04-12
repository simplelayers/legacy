<?php


use utils\ParamUtil;
use model\Plans;
use model\Seats;
use auth\Context;
function _exec() {
    

	$wapi = System::GetWapi ();
	
	$args = $_REQUEST;
	$action = strtolower ( ParamUtil::Get ( $args, 'action' ) );
	$what = strtolower( ParamUtil::Get($args,'what'));
	$format = WAPI::GetFormat ();
	$plans = new Plans();
	$seats = new Seats();
	$ownerSeat = $seats->GetSeatIdByName(Seats::SEATNAME_ORGOWNER);
	switch($action) {
		case 'get_plan_ads':
			switch($what) {
				
			}
			$listData = $plans->GetAdvertisedPlans();
			WAPI::SendSimpleResults( $listData,null,true );
			break;
		case WAPI::ACTION_LIST:
			if(!Context::Get()->IsSysAdmin()) throw new Exception('Unauthorized action; you do not have permission to perform this action');
			$listData = $plans->GetPlans();
				WAPI::SendSimpleResults( $listData,null,true );
			break;
		case WAPI::ACTION_ADD:
			$item = ParamUtil::Get($args,'item',null,true);
			
			try {
				if(!isset($item['owner_seat'])) $item['owner_seat'] = $ownerSeat;
				$plan = $plans->AddPlan($item);
				
				if (! $plan)
					throw new Exception ( 'Creation Problem:There was an unknown problem creating a plan for <i>$name</i>' );
				WAPI::SendSimpleResults(array('document'=>$plan,'action_status'=>'added'));
			} catch ( Exception $e ) {
				if( stripos($e->getMessage(),'duplicate key error')>=0) {
					throw new Exception("Creation Problem: Plan with {$item['planName']} already exists.");
				} 	
			}		
			break;
		case WAPI::ACTION_SAVE:
			list($changes) = ParamUtil::Requires($args,'changeset');

			if(!Context::Get()->IsSysAdmin()) throw new Exception('Unauthorized action; you do not have permission to perform this action');
			foreach($changes as $change) {
			    $plans->UpdatePlan($change);
			}
			WAPI::SendSimpleResults(array('message'=>'updates complete'));
			break;
	}
	
	
}



?>
