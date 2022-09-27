<?php

namespace model;

class RolePermissions extends MongoCRUD {
	
	protected $collectionName = 'role_permissions';

	public function __construct() {
		parent::__construct();
		$this->collection->createIndex(array('data.contextId'=>1,'data.roleId'=>1),array('unique'=>true));
	}
	
		
	public function CreatePermissionsDoc($contextId,$roleId) {
		$document = $this->MakeDocument(array('contextId'=>$contextId,'roleId'=>$roleId,'permissions'=>array()),false,true,false);
		
		$document = $this->SetDefaultPermissions($document);
		$this->Update($document);
		return $this->MakeRef($document);
	
	}
	
	public function GetPermissionsByRef($ref) {
		$rolePermissions = $this->mongo->getDBRef($ref)->Get();
		return $rolePermissions;
	}
	
	public function GetPermissionsByIds($contextId=null,$roleId) {
		
		if(is_null($contextId)) $contextId = Roles::GetDefaultContextId();
		$criteria[] = \Comparisons::ToMongoCriteria('data.contextId',\Comparisons::COMPARE_EQUALS,$contextId);
		$criteria[] = \Comparisons::ToMongoCriteria('data.roleId',\Comparisons::COMPARE_EQUALS,$roleId);
		$criteria= \Comparisons::GroupMongoCriteria($criteria,\Comparisons::OPERATOR_AND);
		$permDoc = $this->FindOneByCriteria($criteria);
		return $permDoc;
	}
	public function GetPermissionDoc($id) {
	
		$criteria = \Comparisons::ToMongoCriteria('id',\Comparisons::COMPARE_EQUALS,$id);
		$rolePermissions = $this->FindByCriteria($criteria);
		foreach($rolePermissions as $permDoc) {
			return $permDoc;
		}
		return null;
	}
	public function SetDefaultPermissions($permDoc,$permissions=null) {
		
		$masterPermissions  =new Permissions();
		$id = $permDoc['id'];
		$permDoc['data']['permissions'] = array();
		
		$permissionSet = &$permDoc['data']['permissions'];
		$masterPermissionList = $masterPermissions->GetPermissions(null,true);
		
		foreach($masterPermissionList as $permission) {
			$ref = $masterPermissions->MakeRef($permission);
			$permissionSet[$permission['id']]=0;
			
		}
		return $permDoc;
		
		
	}
	
	public function GetPermDocs() {
		return $this->FindByCriteria();
	}
	
	public static function GetPermIdsInDoc($doc) {
		if(!$doc['data']) return null;
		if(!$doc['data']['permissions']) return null;
		$permissions = $doc['data']['permissions'];
		return array_keys($permissions);
	}

	
	public function GetPermissions($permIds,$permsRef) {
		$permDoc = null;
		if(is_array($permsRef)) {
			$contextId = $permsRef['contextId'];
			$roleId=$permsRef['roleId'];
			$permDoc = $this->GetPermissionsByIds($contextId, $roleId);
		}elseif(is_a($permsRef,\MongoDBRef)) {
			$permDoc = $this->db->getDBRef($permsRef);
		} else {
			$permDoc = $this->GetPermissionDoc($permDoc);
		}
		$perms = array();
		foreach($permIds as $permId) {
			$perms[$permId] = $permDoc['permissions'][$permId]['value'];
		}
		return $perms;
	}
	
	public function ListPermissions($permDoc,$byName=false,$targets=null,$asKeyVal=false) {
		$permissions = new Permissions();
		$permissionSet = $permissions->GetPermissions();
		$permList = array();
		
		#die();
		foreach($permissionSet as $permission) {
			$id = $permission['id'];
			
			if(!is_null($targets)) {
				if(!in_array($permission['permission'],$targets)){
					continue;
				}
			}
			if($asKeyVal){
				$key = $byName ? $permission['permission'] : $id;
				$permList[$key] = $permDoc['data']['permissions'][$id];				
			} else {
				$key = $byName ? $permission['permission'] : $id;
				$permList[$key] = array('id'=>$id,'name'=>$permission['permission'],'value'=>$permDoc['data']['permissions'][$id]);
			}
			
		}
		
		#var_dump($permList);
		return $permList;
		
		
	}
	
	public function AddPermissions($permDoc,$ids) {
		foreach($ids as $id) {
			$permDoc['data']['permissions'][$id] = 0;
		}
		$this->Update($permDoc);
		
	}
	public function PrunePermissions($permDoc,$ids) {
		foreach($ids as $id) {
			unset($permDoc['data']['permissions'][$id]);
		}
		$this->Update($permDoc);
	}
	
	public function SavePermissions($permId,$permissions) {
		
		$permDoc = $this->GetPermissionDoc($permId);
		foreach($permissions as $key=>$item) {
			$permissions[$key] = $item['value'];			
		}
		$permDoc['data']['permissions'] = $permissions;
		$this->Update($permDoc);
	}
	
}

?>
