<?php

namespace model;

use model\MongoCRUD;
use utils\ParamUtil;

class Permissions
extends MongoCRUD {

	protected $collectionName = 'permissions_masterlist';
	
	const NONE = 0;
	const VIEW = 1;
	const EDIT = 2;
	const COPY = 4;
	const CREATE = 8;
	const SAVE = 16;
	const DELETE = 32;
	const FULL = 63;
	
	/* (non-PHPdoc)
	 * @see \model\MongoCRUD::__construct()
	 */
	public function __construct() {
		// TODO: Auto-generated method stub
		parent::__construct();
		$this->collection->createIndex(array('permission'=>"text"));
	}

	
	
	
	public function AddPermission($permissionPath) {
		 $permissionPath = $this->CorrectPermission($permissionPath);
		$doc = $this->MakeDocument(array('permission'=>trim($permissionPath)),true);		
	}
	
	protected function CorrectPermission($permissionPath) {
		$permissionPath = ":".$permissionPath.":";
		$permissionPath = str_replace('::',':',$permissionPath);
		return $permissionPath;	
	}
	
	public function ImportPermissions($permissionSet) {
		foreach($permissionSet as $permissionPath) {
			$this->AddPermission(permissionPath);
		}
	}
	
	public function RemovePermission($permission) {
	
		if(!ParamUtil::IsAssoc($permission)) return null;
		$id = $permission['id'];
		$this->DeleteItem($id);
		$doc = $this->FindDocumentById($id);
		if(!$doc) return true;
		return false;
	}
	
	public function ProcessChangeset($changes) {
		
		foreach($changes as $change) {
			if(is_null($change)) continue;
			if($change['permission']=='') {
				$this->DeleteItem($change['id']);
				continue;
			}
			if(is_null($change['id'])) {
				$this->AddPermission($change['permission']);
				continue;
			}
			
			if(isset($change['isDeleted'])) {
				if($change['isDeleted']) {
					$this->DeleteItem($change['id']);
					continue;
				}				
			}
			
			
			$doc = array('id'=>$change['id'],'permission'=>$change['permission']);
			$this->UpdatePermission($doc);
			
		}		
		self::MergeChanges();
	}
	
	public function UpdatePermission($docOrDocs) {
		if(ParamUtil::IsAssoc($docOrDocs)) return $this->Update($docOrDocs);
		foreach($docOrDocs as $doc) {
			$this->Update($doc);
		}
	}
	
	public function GetPermissions($targets = null,$include_id=false) {
		if(is_null($targets)) return $this->FindByCriteria(null,null,$include_id,array('permission'=>MongoCRUD::SORT_ASC));
		$criteria = \Comparisons::ToMongoCriteria('permission', \Comparisons::COMPARE_IN,$targets);
		$entries = $this->FindByCriteria($criteria,array('id'=>1),false,array('permission'=>MongoCRUD::SORT_ASC));
		return ParamUtil::GetSubValues($entries, 'id');
	} 
	
	public function GetPermissionSet($permPath) {
		if(substr($permPath,0,1)!=':') $permPath=":".$permPath;
		if(substr($permPath,-1)!=':') $permPath.=':';
		$regex  = new \MongoRegex('/^'.$permPath.'/i');
		return $this->FindByCriteria(array('permission'=>$regex));
	}
	
	public function GetMasterIds() {
		$list = iterator_to_array($this->FindByCriteria());
		$list = ParamUtil::GetSubValues($list,'id');
		return $list;
	}
	
	public static function MergeChanges() {
		$permission  = new Permissions();
		$rolePermission  = new RolePermissions();
		
		$masterIds = $permission->GetMasterIds();
		$docs = $rolePermission->GetPermDocs();
		
		foreach($docs as $doc) {
			#echo "Doc:".$doc['id']."\n";
			$permIds = RolePermissions::GetPermIdsInDoc($doc);
			$missingRolePerms = array_diff($masterIds,$permIds);
			#var_dump($missingRolePerms);
			$rolePermission->AddPermissions($doc,$missingRolePerms);
				
			$doc = $rolePermission->GetPermissionDoc($doc['id']);
			$permIds = RolePermissions::GetPermIdsInDoc($doc);
		
			$excessPerms = array_diff($permIds,$masterIds);
			$rolePermission->PrunePermissions($doc,$excessPerms);
			#var_dump($excessPerms);
			#var_dump('/////');
				
		}
		
	}
	
	public function Reset() {
		$this->collection->remove();
	}
	
	public static function HasPerm($perms,$perm) {
	    
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		$value = 0;
		$permission = 0;
		
		if(!is_array($perm)) $perm = array($perm);
		if(is_array($perm)) {
			foreach($perm as $p) {
				$p = self::CorrectPerm($p);
				if(!isset($perms[$p])) continue;
				$permission = $permission | $perms[$p];
			}			
		}
		
		foreach($args as $val) {
			$value+=$val;
		}
		
		return (($permission & $value)>0);
	}
	
	private static function CorrectPerm($perm) {
		if(substr($perm,0,1)!=':') $perm = ":".$perm;
		if(substr($perm,-1,1)!=':') $perm.=':';
		return $perm;
	}
	
	
	public static function PrefixedHasPerm($perms,$prefix,$subPerms,$value) {
		$result = 0;
		if(!is_array($subPerms))  throw new \Exception('subPerms not array');
		foreach($subPerms as $perm) {
			if(substr($perm,-1)!=':') $perm = $perm.':';
			$result = $result | $perms[$prefix.':'.$perm];
		}
		return (($result & $value) > 0);
	}
	
	public static function PermissionsToItems($permissions) {
	    $items=array();
	    foreach($permissions as $permName=>$permValue) {
	        $items[]=array('name'=>$permName,'value'=>$permValue);
	    }
	    return array('permissions'=>$items);
	}

	
}
