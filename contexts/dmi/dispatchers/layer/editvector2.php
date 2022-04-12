<?php

use model\Permissions;
use model\SeatAssignments;
use model\RolePermissions;
use model\Seats;
/**
 * Process the layereditvector1 form, to save their changes to the layer information.
 * @package Dispatchers
 */
/**
  */
function _config_editvector2() {
	$config = Array();
	// Start config
	// Stop config
	return $config;
}

function _dispatch_editvector2($template, $args) {

    $user = SimpleSession::Get()->GetUser();
    $world = System::Get();
    $layer = $world->getLayerById($_REQUEST['id']);
    if (!$layer or $layer->getPermissionById($user->id) < AccessLevels::EDIT) {
        print javascriptalert('You do not have permission to edit that Layer.');
        return print redirect('layer.list');
    }
    
    if(isset($_REQUEST["contact"])){
        
        $recipient = System::Get()->getPersonById($_REQUEST["contact"]);
        $seatAssignments = new SeatAssignments();
        $seatAssignment  = $seatAssignments->GetAssignment($recipient->id);
        	
        if($seatAssignment) {
            $orgId = $seatAssignment['data']['orgId'];
            $seatId = $seatAssignment['data']['seatId'];
     
            $roleId = SeatAssignments::GetUserRole($recipient->id);
        
            if($roleId) {
                $rolePermissions = new RolePermissions();
                $permissions = $rolePermissions->GetPermissionsByIds(null,$roleId);
                	
                $permissions = $rolePermissions-> ListPermissions($permissions,true,null,true);
                if($recipient) {
                    if(!Permissions::HasPerm($permissions,array(':Layers:Give:'),Permissions::SAVE)) {
                        print javascriptalert($recipient->realname.' does not currently have permission to recieve layers.');                    
                    } else {
                        $layer->setOwner($recipient->id);
                    }
                }
            }
        
        }
    
        
    }
     
    
    // load the layer and verify their access
    
    
    // are they allowed to be doing this at all?
    /*if ($layer->owner->id != $user->id and $user->accounttype < AccountTypes::GOLD) {
        print javascriptalert('You must upgrade your account to edit others\' Layers.');
        return print redirect('layer.list');
    }*/
    
    // handle the simple attributes
    $layer->name        = $_REQUEST['name'];
    $layer->searchtip = $_REQUEST['searchtip'];
    $layer->description = $_REQUEST['description'];
    $layer->tags    = $_REQUEST['tags'];
    $layer->minscale = $_REQUEST['minscale'] == "none" ? null : $_REQUEST['minscale'];
    $layer->tooltip  =$_REQUEST['tooltip'];
    if(isset($_REQUEST['rich_tooltip'])) {
	$layer->rich_tooltip = $_REQUEST['rich_tooltip'];
    }
    if($_REQUEST['defaultsearch']){
    	$layer->default_criteria = $_REQUEST['search_field'].';'.$_REQUEST['comparison'].';'.$_REQUEST['searchVal'];
    }else{
    	$layer->default_criteria = null;
    }
    $layerid = $_REQUEST['id'];
    #System::GetDB()->debug=true;
    // done -- keep them on the details page or send them to their layerbookmark list, depending
    // on whether they own the layer they just edited
    $layer->owner->notify($user->id, "edited layer:", $layer->name, $layer->id, "./?do=layer.info&id=".$layer->id, 5);
    $reportEntry = \Report::MakeEntry(REPORT_ACTIVITY_UPDATE,REPORT_ENVIRONMENT_DMI,REPORT_TARGET_LAYER,$layer->id,$layer->name,$user);
   
    
    $report = new \Report($sys,$reportEntry);
    $report->commit();
    returnToLayer();
    print redirect($layer->owner->id == $user->id ? 'layer.editvector1&id='.$layerid : 'layer.list');
}

function returnToLayer() {
	return print redirect('layer.editvector1&id='.RequestUtil::Get('id'));
}

?>

