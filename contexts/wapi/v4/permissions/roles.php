<?php

use utils\ParamUtil;
use model\Roles;
use model\RolePermissions;

function _exec() {
	
	define ( 'WHAT_CONTEXT', 'context' );
	define ( 'WHAT_ROLE', 'role' );
	define ( 'WHAT_PERMISSION','permission');

	$wapi = System::GetWapi ();

	$args = $_REQUEST;
	$action = strtolower ( ParamUtil::Get ( $args, 'action' ) );

	$roles = new Roles ();
	$format = WAPI::GetFormat ();
	switch ($action) {
            case WAPI::ACTION_SAVE:
                    switch($what) {
                            case WHAT_CONTEXT:
                                    list($changes) = ParamUtil::Requires($args,'changeset');
                                var_dump($changes);
                                die();
                                    foreach($changes as $change) {
                                            $roles->UpdateRoleContext($change);
                                    }
                                    WAPI::SendSimpleResults(array('message'=>'updates complete'));
                                    break;
                            case WHAT_ROLE:
                                    list($changes) = ParamUtil::Requires($args,'changeset');
                                    $firstChange = $changes[0];

                                    $context = $roles->GetRoleContextByRole($firstChange['id']);

                                    foreach($changes as $change) {
                                            $roles->UpdateRole($context,$change);
                                    }
                                    WAPI::SendSimpleResults(array('message'=>'updates complete'));
                                    break;
                            case WHAT_PERMISSION:
                                    list($changes) = ParamUtil::Requires($args,'changeset');
                                    $role = $changes['role'];
                                    SimpleSession::Get()->UpdateSessionsByRole($role['id']);
                                    $permDocId = $role['permissions']['$id']['$id'];
                                    $rolePermissions = new RolePermissions();

                                    $rolePermissions->SavePermissions($permDocId,$changes['permissions']);
                                    WAPI::SendSimpleResults(array('message'=>'updates complete'));
                                    break;
                    }
		case WAPI::ACTION_LIST :
			$what = ParamUtil::Get ( $args, 'list' );
			switch ($what) {
				case WHAT_CONTEXT :
					$listData = $roles->GetRoleContexts ();
					WAPI::SendSimpleResults ( $listData );
					break;
				case WHAT_ROLE :
					$contextId = ParamUtil::Get($_REQUEST,'contextId');
					if($contextId == 'default') $contextId = null;
					$listData = $roles->GetRolesByContext ( $contextId );
                                        
					WAPI::SendSimpleResults ( $listData );
					break;
				case WHAT_PERMISSION:
					list($permissionsId) = ParamUtil::Requires( $_REQUEST,'permissionsId');
					$rolePerms = new RolePermissions();
					$perms = $rolePerms->GetPermissionDoc($permissionsId);
					WAPI::SendSimpleResults($rolePerms->ListPermissions($perms));
						
					break;
						
			}
			break;
		case WAPI::ACTION_ADD :
			$what = ParamUtil::Get ( $args, 'add' );
			try {
				switch ($what) {
					case WHAT_CONTEXT :
						$contextName = RequestUtil::Get ( 'contextName' );
						if (is_null ( $contextName )) {
							throw new Exception ( "Parameter not set: expected a value for parameter <li>contextName</li>" );
						}
						$document = $roles->AddRoleContext ( $contextName );
						if (! $document)
							throw new Exception ( 'Creation Problem:There was an unknown problem creating a role context for <i>$contextName</i>' );
						WAPI::SendSimpleResults ( array('document'=>$document,'action_status'=>'added') );
						break;
					case WHAT_ROLE :
						$contextId = RequestUtil::Get ( 'contextId' );
						if (is_null ( $contextId )) {
							throw new Exception ( "Parameter not set: expected a value for parameter <li>contextId</li>" );
						}
						$roleName = RequestUtil::Get ( 'role' );
						if (is_null ( $roleName )) {
							throw new Exception ( "Parameter not set: expected a value for parameter <li>roleName</li>" );
						}
                                                 
						$roleContext = $roles->AddRole($roleName,$contextId);
						WAPI::SendSimpleResults(array('document'=>$roleContext,'action_status'=>'added'));
						break;
				}
			} catch ( Exception $e ) {
				if( stripos($e->getMessage(),'duplicate key error')>=0) {
					switch($what) {
						case WHAT_CONTEXT:
							WAPI::SendSimpleResults(array('document'=>$roles->GetRoleContextByName($contextName),'action_status'=>'exists'));
							break;
						case WHAT_ROLE:
							$contextId = RequestUtil::Get ( 'contextId' );
							$roleContext = $roles->GetRoleContext($contextId);
							WAPI::SendSimpleResults(array('document'=>$roleContext,'action_status'=>'exists'));
							break;
					}
						
				}
			}
				
			break;


	}
}
?>