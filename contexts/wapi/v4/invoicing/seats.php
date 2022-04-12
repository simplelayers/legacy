<?php


use utils\ParamUtil;
use model\Seats;
function _exec() {


	
	$wapi = System::GetWapi ();
	
	$args = $_REQUEST;
	$action = strtolower ( ParamUtil::Get ( $args, 'action' ) );
	$format = WAPI::GetFormat ();
	$seats = new Seats();
	
	switch($action) {
		case WAPI::ACTION_LIST:
			$listData = $seats->GetSeats();
			WAPI::SendSimpleResults( $listData,null,true );
			break;
		case WAPI::ACTION_ADD:
			try {
				list($name,$role) = ParamUtil::Requires($_REQUEST,'seatName','roleId');
				$seat = $seats->AddSeat(array('seatName'=>$name,'roleId'=>$role));
				if (! $seat)
					throw new Exception ( 'Creation Problem:There was an unknown problem creating a seat for <i>$name</i>' );
				WAPI::SendSimpleResults(array('document'=>$seat,'action_status'=>'added'));
			} catch ( Exception $e ) {
				if( stripos($e->getMessage(),'duplicate key error')>=0) {
					throw new Exception("Creation Problem: Seat with name $name already exists.");
				} 	
			}		
			break;
		case WAPI::ACTION_SAVE:
			list($changes) = ParamUtil::Requires($args,'changeset');
			foreach($changes as $change) {
				$seats->UpdateSeat($change);
			}
			WAPI::SendSimpleResults(array('message'=>'updates complete'));
			break;
	}
	
	
}



?>