<?php
namespace model;
use model\records\SharedItem;
	/*
 * Created on Sep 15, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class ShareableCRUD
 extends CRUD {
 	protected $table_sharing;
 	protected $table_sharing_groups;
 	protected $itemField;	

 	public function __construct() {
 		
		//$this->table = null;
		//$this->idField = null;
		//$this->nameField = null;
		//$this->pre_check_params = array();
		//$this->hasModified = false;
 		//$this->table_sharing = "";
 		//$this->table_sharing_groups = "";
 	}
 	
 	protected function IsReady( $optReady  =true)
 	{
 		$ready = $optReady;
 		$ready  = isset($this->table_sharing);
 		$ready = ($ready && !is_null($this->table_sharing_groups));
 		$ready = ($ready && !is_null($this->itemField));
 		if(!$ready) throw new \Exception('Order of operations error:'.get_class($this).' - Attempting table operations with insufficient data.');
 		parent::IsReady($ready);	
 	}
 	
 	public function GetGroupItems($groupid,$optField=null,$optValue=null) {
 		self::IsReady();
 		$query = "SELECT * FROM {$this->table_sharing_groups} WHERE group_id=?";
 		$vals = array($groupid);
 		if(!is_null($optField) and !is_null($optValue)) {
 			$query.=" AND $optField=?";
 			array_push($vals,$optValue);
 		}
 		$results =\System::Get()->db->Execute($query, array($groupid));
 		$results = ($results) ? $results->getRows() : array();
 		return $this->GetObjectArray($results);
 	}
 	
 	public function GetUserItems($userid ) {
 		self::IsReady();
 		
 		$query1 = "SELECT * FROM {$this->table} WHERE owner=?";
 		$results = \System::Get()->db->Execute($query1,array($userid)); 		

 		$results  = ($results) ? $results->getRows() : array();
 		$query2 = "SELECT * FROM {$this->table_sharing} WHERE who=? and permission>0";
 		$results2 = \System::Get()->db->Execute($query2,array($userid));
 		$results2  = ($results) ? $results2->getRows() : array();

 		return $this->GetObjectArray(array_merge($results,$results2));
 	}
 	
 	public function GetPermissionByUser($user, SharedItem $item) {
 		$id= null;
 		$itemId = null;
 		$idField = $this->idField;
 		
 		if(!($item instanceof SharedItem)) throw new \Exception('Invalid parameter:'.get_class($this).' - item ('.get_class($item).') not an instance of SharedItem.');
		$itemId = is_null($item->$idField) ? null : $item->$idField;
		if(is_null($itemId)) throw new\Exception('Invalid parameter:'.get_class($this).' - specified idField ($idField) not found in specified object('.get_class($item).')'); 		 
 		
 		if( $user instanceof \Person) $id = $user->id;
 		elseif( gettype($user)=='string') $id =\System::Get()->getPersonByUsername($user)->id;
 		if(is_null($id)) throw new \Exception('Invalid parameter:'.get_class($this).' - user id could not be determined from supplied parameters.');
 		
 		$query = "SELECT * from {$this->table_sharing} WHERE user=? and {$this->itemField}=?";
 		$record =\System::Get()->db->getRow($query,array($id,$itemId));
		return $this->GetPermission(\System::Get(),$record);
 	}
 	
 	public function GetPermissionByGroup($group, SharedItem $item){
 		$id= null;
 		$itemId = null;
 		
 		if(!($item instanceof SharedItem)) throw new \Exception('Invalid parameter:'.get_class($this).' - item ('.get_class($item).') not an instance of SharedItem.');
 		$idField = $this->idField;
		$itemId = is_null($item->$idField) ? null : $item->$idField;
		if(is_null($itemId)) throw new \Exception('Invalid parameter:'.get_class($this).' - specified idField ($idField) not found in specified object('.get_class($item).')'); 		 
 		
 		if( $group instanceof \SocialGroup ) $id = $user->id;
 		elseif( gettype($group)=='string') $id =\System::Get()->getPersonByUsername($group)->id;
 		if(is_null($id)) throw new \Exception('Invalid parameter:'.get_class($this).' - group id could not be determined from supplied parameters.');
 		
 		$query = "SELECT * from {$this->table_sharing_groups} WHERE group=? and $this->itemField=?";
 		$record =\System::Get()->db->getRow($query,array($id,$itemId));
		return $this->GetPermission(\System::Get(),$record);
 			
 	}
 	
 	public function SetPermissionByUser($user,$item,$level)
 	{
 		$id= null;
 		$itemId = null;
 		$idField = $this->idField;
 		
 		if(!($item instanceof SharedItem)) throw new \Exception('Invalid parameter:'.get_class($this).' - item ('.get_class($item).') not an instance of SharedItem.');
		$itemId = is_null($item->$idField) ? null : $item->$idField;
		if(is_null($itemId)) throw new \Exception('Invalid parameter:'.get_class($this).' - specified idField ($idField) not found in specified object('.get_class($item).')'); 		 
 		
 		if( $user instanceof \Person ) $id = $user->id;
 		elseif( gettype($user)=='string') $id =\System::Get()->getPersonByUsername($user)->id;
 		if(is_null($id)) throw new \Exception('Invalid parameter:'.get_class($this).' - user id could not be determined from supplied parameters.');
 		
 		$query = "Update {$this->table_sharing} SET permission=? WHERE user=? and $idField=?";
 		\System::Get()->db->Execute($query,array($level,$id,$itemId));
		return $this->GetPermissionByUser($user,$item);
 	}
 	
 	public function SetPermissionByGroup($group,$item,$level)
 	{
		$id= null;
 		$itemId = null;
 		
 		if(!($item instanceof SharedItem)) throw new \Exception('Invalid parameter:'.get_class($this).' - item ('.get_class($item).') not an instance of SharedItem.');
 		$idField = $this->idField;
		$itemId = is_null($item->$idField) ? null : $item->$idField;
		if(is_null($itemId)) throw new \Exception('Invalid parameter:'.get_class($this).' - specified idField ($idField) not found in specified object('.get_class($item).')'); 		 
 		
 		if( $group instanceof \SocialGroup ) $id = $user->id;
 		elseif( gettype($group)=='string') $id =\System::Get()->getPersonByUsername($group)->id;
 		if(is_null($id)) throw new \Exception('Invalid parameter:'.get_class($this).' - group id could not be determined from supplied parameters.');
 		
 		$query = "UPDATE {$this->table_sharing_groups} WHERE group=? and {$this->$itemField}=?";
 		$record =\System::Get()->db->getRow($query,array($id,$itemId));
		return $this->GetPermission($record);
 	}
 	
 	
	public function Search($criteria ,$cols="*", &$paging=null, $method="OR", $order=null,$exact=false,\Person $who=null) {
		if($who == null ) throw new \Exception("Invalid search execution:".get_class($this)."who parameter not provided, must be a Person object instance- $cols");
 		$this->IsReady();
 		
 		if(preg_match('/[\.\(\);\[\]]/i',$cols)) throw new \Exception(("Invalid search criteria:".get_class($this)."cols parameter contains invalid characters - $cols"));
 	
 		if( $paging == null ) $paging = new \Paging();
	    $idCheck = $this->doIdSearchExclusion ? $this->idField.'!=0 AND ' : '';
	    
	    $source = $this->table;
	    
	    
	    
	    $left = $this->table;
	    $right = $this->table_sharing;								

	   
	    $countQuery = "select count(*) from $left left join $right on $left.id=$right.{$this->itemField} WHERE ($right.permission>0 AND $right.who={$who->id}) OR ($left.owner={$who->id}) AND(";
	    $query = "select $left.*,$right.permission from $left left join $right on $left.id=$right.{$this->itemField} WHERE ($right.permission>0 AND $right.who={$who->id}) OR ($left.owner={$who->id}) AND (";
		$limit = $paging->toQueryString();
		$order = is_null($order) ? "": "ORDER BY $order";
		
		//$countQuery = "";
		$count = $paging->count;
		$args = array();
		
			
 		$compareStrings=array();
 		$isFirst = true;
 		$compareStrings = "";

		$nominalOperators = array("<","<=",">",">=",">");
		
	 	foreach( $criteria as $field=>$value )
		{
				list($operator,$val) = explode(',',$value);
				$value = strtolower($val);
				$prefix = ($isFirst) ? "" : $method;
				if(in_array($operator,$nominalOperators)) {
					$matchType = $operator;				
					$query.= "$prefix $field $matchType ? ";
					$countQuery.="$prefix $field $matchType ? ";
					$args[] = $value;
				} else {
				
			 		$matchType = $exact ? "=": "LIKE"; 
					$query.= "$prefix lower($field) $matchType ? ";
					$countQuery.="$prefix lower($field) $matchType ? ";
					$args[] = ($exact) ? $value : "%$value%";
				}

		}
		$query.=")";
		$countQuery.=")";

		//error_log($query);

		if($count == null ) {
		
			$count =\System::Get()->db->Execute($countQuery,$args);
			$count = ($count) ? $count->getRows() : 0;
			
		  	if($count!=0) $count = $count[0]['count'];
		}
		
		
		$results =\System::Get()->db->Execute($query,$args);
		$results = ($results) ? $results->getRows() : array();
		
		$paging->setResults($results, $count );


		return $this->GetObjectArray($results);
	}
 
 	
 	protected function GetPermission( $record ) {
 		//return new Permission(System::Get(),$record);
 	}
 	
 	public function GetObject($record) {
 		$this->IsReady();
 		return new SharedItem($record,$this->table);
 		
 	}
 	
 }
 

 
?>
