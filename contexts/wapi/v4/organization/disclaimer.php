<?php
use utils\ParamUtil;
use model\SeatAssignments;
use model\License;



function _exec() {
	$wapi = System::GetWapi ();
	
	$args = $_REQUEST;
	$action = strtolower ( ParamUtil::Get ( $args, 'action' ) );
	$orgId = ParamUtil::Get ( $_REQUEST, 'orgId' );
	$org = Organization::GetOrg($orgId);
	
	switch ($action) {
		case WAPI::ACTION_GET:
	       echo base64_decode($org->disclaimer);		
		  break;
	
	}
}

?>