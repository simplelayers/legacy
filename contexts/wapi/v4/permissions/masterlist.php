<?php


use model\Permissions;
use utils\ParamUtil;

function _exec() {
	$wapi = System::GetWapi();
	#$wapi->RequireAdmin();

	$permissions = new Permissions();
	
	$args = $_REQUEST;
	ParamUtil::Requires($args,'request');
	$request = ParamUtil::Get($args,'request');
	switch($request) {
		case WAPI::ACTION_IMPORT://'import':
			$perms = ParamUtil::Get($args,'data');
			$perms = str_replace("\r","",$perms);
			$perms = str_replace("\/",':',$perms);
			$perms = explode("\n",$perms);
			
			foreach($perms as $perm) {
				$permissions->AddPermission($perm);
			}
			// Note, lack of break is so we return full permission list as
			// is accomplished by the 'list' case			
		case WAPI::ACTION_CHANGESET:
		case WAPI::ACTION_SAVE:
			$data = ParamUtil::GetJSON($args,'permissions',null);
			if(!is_null($data))  $permissions->ProcessChangeset($data);
			// Note, lack of break is so we return a full permission list as
			// is accomplished by the 'list' case.
		case WAPI::ACTION_LIST:
			$groupby = ParamUtil::Get($args,'groupby');
			if($groupby) {
				$permissionSet = $permissions->GetPermissionSet($groupby);
			} else {
				$permissionSet = $permissions->GetPermissions();
			}
			return WAPI::MongoResultsToJSON($permissionSet);
			break;
		
		case WAPI::ACTION_UPDATE: 
			$permission = json_decode(ParamUtil::Get($args,'permission'));
			$permissions->UpdatePermission($permission);
			return WAPI::SendSimpleResponse(array());
			break;
		case WAPI::ACTION_DELETE:
			$permissionId = ParamUtil::Get($args,'id');
			$permissions->DeleteItem($permissionId);
			return WAPI::SendSimpleResponse(array());
			break;
		case WAPI::ACTION_RESET:
			$permissions->Reset();
			$perms = $permissions->GetPermissions();
			if($perms->count==0) {
				return WAPI::SendSimpleResponse(array());
			}
			throw new Exception('Removal Problem: There was a problem removing some permissions');
			break;
		
	}
}