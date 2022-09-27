<?php

namespace model;

use model\records\CachedRecord;
/*
 * Created on Sep 14, 2009
 *
 */
abstract class CRUD {
	
	protected $table;
	
	protected $world;
	protected $pre_check_params = array('short_name');
	protected $getFunction;
	protected $idField;
	protected $nameField;
	protected $hasModified;
	protected $creationFields = array();
	protected $isReadOnly = true;
	protected $doIdSearchExclusion= true;
	protected $hasSearchableField = false;
	protected $isPrivate = false;
	protected $objectId = 0;
	protected $arrayOfObjectsRetrivedRowData = Array();
	protected $backupField;	
	protected $backupTime;
	
	protected $modifiedField = 'modified';
	protected $numericFields = array();
	public $linkedFields = array();

	const EXISTS = -1;
	
	protected function __construct($world=null ) {
		$this->world = \System::Get();
		//$this->table = null;
		//$this->idField = null;
		//$this->nameField = null;
		//$this->hasModified = false;
		//$this->creationFields = array();
		//$this->isReadyOnly = false
	}
	
	public function __get($name) {
		if($name == 'isPrivate' ) return $this->isPrivate;
		if($name == 'id' ) return $this->objectId;
	}

	
	function Backup($suffix='_cg_bak') {
		if(is_null($this->backupField)) {
			throw new \Exception('Backup Failure: backupField not set');
		}
		$bakupTable = $this->table.$suffix;
		$this->{$this->backupField} = $bakupTable;
		$db = \System::GetDB(\System::DB_ACCOUNT_SU);
		$db->Execute('DROP TABLE IF EXISTS $backupTable');
		$db->Execute('CREATE TABLE $backupTable AS TABLE '.$this->table);
		$db->Execute('UPDATE TABLE '.$this->table.' SET '.$this->backupField.' = NOW() WHERE id='.$this->id);
	}
	
	
	protected function IsReady($optReady=true) {
		$ready =  $optReady;
		$ready = ($ready && isset($this->table));
		//$ready = ($ready && isset($this->getFunction));
		$ready = ($ready && isset($this->idField));
		if(!$ready) throw new \Exception('Object not ready:'.get_class($this).' - Attempting to perform an action on object that has not been properly initialized. Or, attempting to perform operation on a base class intended to be extended.');
		return $ready; 
	}
	
	public function Create( $params) {
		$this->IsReady();
		

		$preCheckQuery = (sizeof($this->pre_check_params)>0) ? "SELECT COUNT(*) from {$this->table} WHERE " : "";
		
		if($preCheckQuery != "")
		{
			$preCheckValues = array();
			foreach($this->pre_check_params as $key) {
				$preCheckQuery.= "$key=? ";
				if(!isset($params[$key])) throw new \Exception("Missing parameter:".get_class($this).": parameter required for pre-creation-check is missing ($key). Provided parameters: ".var_export($params,true));
				array_push($preCheckValues,$params[$key]);
			}
			$result = $this->world->db->GetRow($preCheckQuery,$preCheckValues);
			$count = $result['count'];
			if($count>0) return self::EXISTS; 

		}
		
		$query = "INSERT INTO {$this->table} (";
		$queryVals = array();
		$placeholders = array();
		foreach($params as $key=>$val ) {
			if($key=='id') continue;
			
			if( in_array( $key, $this->creationFields) ) {
				$query.=$key.',';
				
				array_push($placeholders,"?");
				array_push($queryVals,''.$val);
			}
			
		}
		
		if($this->hasModified) {
			$query.=$this->modifiedField;
			array_push($placeholders,'now()');				
			
		} else {
			$query = substr($query,0,strlen($query)-1);
		}
		$query.= ") VALUES(".implode(',',$placeholders).")";
		
		$id = $this->world->db->GetOne($query." Returning id",$queryVals);
		
		return ($id)? $this->RetrieveById($id)  : null;	
  	}
  	
  	public function ProcessChangeset(\SimpleXMLElement $changeSet) {  		
  		$deletions = $changeSet->deleted[0]->children();
		$additions = $changeSet->created[0]->children();
		$updates =   $changeSet->updated[0]->children();
		$results = array();
		
		foreach( $deletions as $deleted )
		{
			$id = $deleted[ $this->idField ];
			$this->Delete($id);
		}
	
		foreach( $additions as $added )
		{
			if(isset($added['owner']) ) $added['owner'] = \SimpleSession::Get()->GetUser();

			$params = array();
			
			foreach( $this->creationFields as $field) {
			
				$params[$field] = $added[$field];
			}
			$result = $this->Create($params);
			
			if($result === -1) {
				error_log($this->db->ErrorMsg());
				continue;
			}
			array_push($results,$result);			
		}
	
		foreach( $updates as $update )
		{
			array_push($results,$this->Update($update));
		}
		return $results;
  	}
  	
  	public function Count() {
  		$count = $this->world->db->getOne('SELECT count(*) as count from '.$this->table);
  		return (int) $count;
  		
  	}
  	
  	public function RetrieveById($id) {
  		$this->IsReady();
  		$query = "SELECT * from {$this->table} WHERE {$this->idField}=?";
  		$result = $this->world->db->GetRow($query,array($id)); 		
  		return (!$result ) ? null : $this->GetObject($result);
  		
  	}
  	/**
  	 * @return SL_Query
  	 */
  	public function NewQuery() {
  	    $query = new SL_Query();
  	    $query->NewTableQuery($this->table);
  	    return $query;
  	    
  	}
  	
  	
  	public function RetrieveByName($name) {
  		$this->IsReady();
  		if(!isset($this->nameField)) return null;
  		$query = "SELECT * from {$this->table} WHERE {$this->nameField}=?";
  		$result = $this->world->db->GetRow($query,array($name));
  		return (!$result) ? null : $this->GetObject($result);
  	}
  	
  	public function RetrieveByField($field,$val) {
  		$this->IsReady();
  		$query = "SELECT * from {$this->table} WHERE $field=?";
  		$result = $this->world->db->GetRow($query,array($val));
  		return (!$result) ? null : $this->GetObject($result);
  	}
  	
  	public function RetrieveFieldByField($field,$val) {
  		$this->IsReady();
  		$query = "SELECT $field from {$this->table} WHERE $field=?";
  		$result = $this->world->db->GetRow($query,array($val));
  		return (!result) ? null : $this->GetObject($result);
  	}
  	
  	public function Update($updates) {
  		$this->IsReady();
  		$set = "";
 		$vals = array();
 		if( $this->hasModified && isset($params[$this->modifiedField])) unset($params[$this->modifiedField]);
		$countOfTheNumberOfUpdates = count($updates);
		$i = 1;
 		foreach($updates as $key=>$val) {
 			if($key==$this->idField) {$id=$val; continue;}
 			$set.="$key=?".($i++ < $countOfTheNumberOfUpdates ? ', ' : '');
 			array_push($vals,$val);
 		}
 		$modified = $this->hasModified ? ",modified=now()" : "";
 		array_push($vals,$this->objectId);
 		
 		$result = $this->world->db->Execute("UPDATE {$this->table} SET $set $modified WHERE {$this->idField}=?",$vals );
 			
  	}
  	
  	// document is an array derived from json as would appear in a mongo db
  	// the purpose of this function is to do an update query based on an array retrieved from json
  	// or that may have been, or will be, part of a mongo db.
  	public function UpdateDoc($document) {
  		//if(isset($document['isDeleted'])) return $this->Delete($document['id']);
  		if(!isset($document['isChanged'])) return;
  		unset($document['isChanged']);
  		
  		if( $this->hasModified) if(isset($document['modified'])) unset($document['modified']);
  		foreach($document as $key=>$val) {
  			if(substr($key,0,1)=='$') {
  				unset($document[$key]);
  			}
  		}
  		$modified = $this->hasModified ? ",modified=now()" : "";
  		$rs = $this->world->db->Execute("select * from {$this->table} WHERE {$this->idField}={$document['id']}");
		$updateSQL = $this->world->db->GetUpdateSQL($rs, $document);
		
  		$result = $this->world->db->Execute($updateSQL);
  		
  	}
  	
  	public function Delete($id=null){
  		if(!$id) $id = $this->objectId;
  		$this->IsReady();
  		$query = "DELETE FROM {$this->table} WHERE {$this->idField}=?";
  		$result = $this->world->db->Execute($query,array($this->objectId));
  		$this->CleanUp();
  	}
  	
  	public static function DeleteObject($id,$table) {
  		$db = \System::GetDB();
  		$result = $db->Execute('DELTE FROM '.$table.' WHERE '.$id.'='.$id);
  		return $result;
  	}
  	
  	public function Refresh(CachedRecord $record) {
  	    $idField=$this->idField;
  		$id = $record->$idField;
  		$newRecord = $this->RetrieveById($id);
  		$record->Refresh($newRecord);
  	}
  	
  	public function  toXMLAttributes()
	{
		$attTemplate = '%s="%s" ';
		$attributes = "";
		$attributes .= sprintf($attTemplate,'linkedFields',implode($this->linkedFields));
		$attributes .= sprintf($attTemplate,'idField',$this->idField);
		$attributes .= sprintf($attTemplate, 'nameField',$this->nameField);
		return $attributes;
	}
	
	public function Search($criteria ,$cols="*", &$paging=null, $method="OR", $order=null,$exact=false,$user=null,$asObjectArray=true ) {
 		$this->IsReady();
 		if(preg_match('/[\.\(\);\[\]]/i',$cols)) throw new \Exception("Invalid search criteria:".get_class($this)."cols parameter contains invalid characters - $cols");
 	
 		if( $paging == null ) $paging = new \Paging();
	    $idCheck = $this->doIdSearchExclusion ? $this->idField.'!=0 AND ' : '';
	    
	    $source = $this->table;
	    
	    
	    #select layers.* from layers join layersharing on layers.id=layersharing.layer where layersharing.who=344 and layersharing.permission>0;
	    
	    

	    $searchCheck = "";
	    if($this->hasSearchableField) {
	        $searchCheck = " (searchable=true or {$this->table}.user={$user->id}) AND ";
	    	if( !is_null($user) ) {
	    		if($user->id == 0) $searchCheck = ""; 
	    		
	    	}
	    } 
	    
	    $countQuery = "SELECT COUNT(*) FROM {$this->table} WHERE $idCheck$searchCheck ";
	    $query ="SELECT $cols FROM {$this->table} WHERE $idCheck$searchCheck (";
	     
	    $limit = $paging->toQueryString();
		$order = is_null($order) ? "": "ORDER BY $order";

		//$countQuery = "";
		$count = (!$paging->isNull()) ? $paging->count : null;
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
					
					$query.= "$prefix ";
					$query.= ($exact)? $field : "lower($field)";
					$query.= "$matchType ? ";
					$countQuery.="$prefix ";
					$countQuery.=($exact) ? $field : "lower($field) ";
					$countQuery.="$matchType ? ";
					$args[] = ($exact) ? $value : "%$value%";
				}

		}
		$query.=")";
		$countQuery.="";


		if(is_null($count) ) {
		
			$count = $this->world->db->GetRow($countQuery,$args);
			$count = (!is_null($count)) ? $count['count'] : 0;
		}
		
		
		$results = $this->world->db->Execute($query,$args);
		
		$results = ($results) ? $results->getRows() : array();
		
		$paging->setResults($results, $count );


		return ($asObjectArray) ? $this->GetObjectArray($results) : $results;
	
	}
	
	function SearchByCriteria( \SearchCriteria $criteria,$countOnly=false,$pxBox=null,$gids=null,$intersectionMode=0) {
	    $criteria->SetNumericFields($this->numericFields);
	    $where = $criteria->GetQuery(false,$pxBox,$gids,$intersectionMode);
	   
	 
	    $db = \System::GetDB();
	    #var_dump($where);
	    
	     
	    $paging = $criteria->paging;
	    
	    $pagingData = array();
	    $results = $db->Execute($where);
	    if(!$paging->isNull()) {
	        $numRecords = $db->GetOne($criteria->GetCountQuery(false));
	        if($countOnly) {
	            return array('num_matches'=>$numRecords);
	        }
	        
	        $paging->setResults($results,$numRecords);
	        $paging->mergeData($pagingData);
	        return array('results'=>$results,'pagingData'=>$pagingData);
	    }
	    
	    return array('results'=>$results);
	     
	}
	
	protected function GetObjectArray($results) {
		$objects = array();
		foreach($results as $result) {
			
			$objects[] = $this->GetObject($result);

		}
		return $objects;
	}
  	
  	protected function GetObject($result) {
  		// override and specify how to return an object for this type of CRUD result.
  		return $result;
  	}
  	
  	protected function CleanUp() {
  		$this->world->db->Execute('VACUUM '.$this->table);
  	}
  	
  	function getAllFieldsAsArray(){
		$query = "SELECT * FROM {$this->table} WHERE {$this->idField} =?";
		$results = $this->world->db->Execute($query, Array($this->objectId));
		if(!$results) return $results;
		if($results->fields[$this->idField] != $this->objectId) return null;
		foreach($results as $result){
			return $result;
		}
		return null;
	}
}
 
?>
