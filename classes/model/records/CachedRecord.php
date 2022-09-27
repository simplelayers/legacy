<?php
/*
 * Created on Sep 15, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

namespace model\records;

 class CachedRecord
 implements \ArrayAccess
 {
 	protected $record;
 	protected $table;
 	protected $hasModified = false;
 	protected $private_fields = array();
 	protected $readOnly_fields = array();
 	protected $boolean_fields = array();
	protected $nameField = '';
	protected $idField ='id';
	
	
	const ID = 'id';
	const USER_ID = 'userid';
	const USER = 'user';
	const OWNER = 'owner';
	const WHO = 'who';
	const GROUP = 'group';
	const GROUP_ID = 'groupid';
	const PROJECT_ID ='projectid';
	const PROJECT = 'project';
	const LAYER_ID = 'layerid';
	const LAYER = 'layer';
	const ORG_ID = 'organizationid';
	const ORG = 'organization';
	
 	public function __construct($record,$table=null) {
 		
 		$this->record = $record;
 		$this->table = $table; 		
 		//$this->hasModified = true;
 		//$this->idField = null;
 		//$this->nameField = null;
 		//$this->private_fields = array();
 		//$this->readOnly_fields = array();
 		//$this->boolean_fields = array();
 			
 	}
 	
 	
 	public function IsReady($optReady=true){
 		$ready = ($optReady) ? true : false;
 		$ready = ($ready and !is_null($this->table));
 		if(!$ready) throw new \Exception('Object not ready:'.get_class($this).' Attempting to perform an action on object that has not been properly initialized. Or, attempting to perform operation on a base class intended to be extended.'); 		
 	}
 	
 	public function Refresh(CachedRecord $record) {
 		$this->record = $record;
 	}
 	
 	
 	/**
 	 * Global getter allows for the following predefined fields to be retrievable for a cached record.
 	 * ->id - the record id from the field designated idField
 	 * ->userid - the numeric id from field userid, user, or owner (in that order).
 	 * ->user, ->owner, ->who - a Person object based on the object's user id..
 	 * ->group - if a group_id field is present an Group object is returned.
 	 * ->service - future tech, external service 
 	 * ->project_id - if there is a project field in the record the id stored in it.
 	 * ->project - if there is a project field, a  Project object.
 	 * ->layerid - if there is a layer field, the id for that layer.
 	 * ->layer - if there is a layer field a Layer object for that layer.
 	 * @param string $field
 	 * @return mixed
 	 */
 	public function __get($field ) {
 		if(gettype($field) != 'string') return null;
 		if(!isset($this->record)) return null;
 		$system = \System::Get();
 		//if(!isset($this->record[$field])) return null;
 		switch($field) {
 			case self::ID: 			
 				if(isset($this->record[$this->idField])) return $this->record[$this->idField];
					return null;
			case self::USER_ID:
				$user=null;
				if(isset($this->record['userid'])) return $this->record['userid'];
				elseif( isset($this->record['user']) ) return $this->record['user'];
				elseif (isset($this->record['owner'])) return $this->record['owner'];
				return null;
			case self::USER:
			case self::OWNER:
			case self::WHO:
				$user = $this->userid;				
				if(isset($this->record[$field.'_obj'])) return $this->record[$field.'obj'];
				$this->record[$field.'_obj'] =  $system->getPersonById($this->record[$field]);
				return $this->record[$field.'_obj'];	
			case self::GROUP:
				if(!isset($this->group_id)) return null;
				if(isset($this->record[$field])) return $this->record[$field];
				$this->record[$field] = $system->getGroupById($this->record['group_id']);				
				break;
			case self::GROUP_ID:
				if(!isset($this->group_id)) return null;
				if(isset($this->record[$field])) return $this->record[$field];
				break;
			case self::PROJECT_ID:
				if(isset($this->record['project'])) return $this->record['project'];
				return null;
			case self::PROJECT:					
				if(!isset($this->projectid)) return null;
				if(isset($this->record[$field.'_obj'])) return $this->record[$field.'_obj'];
				$this->record[$field.'_obj'] = $system->getProjectById($this->projectid);
				break;
			case self::LAYER_ID:
				if(isset($this->record['layer'])) return $this->record['layer'];
				return null;
			case self::LAYER:
				if(!isset($this->layerid)) return null;
				if(isset($this->record[$field.'_obj'])) return $this->record[$field.'_obj'];
				$this->record[$field.'_obj'] = $system->getLayerById($this->layerid);
				break;
			case self::ORG:
				if(isset($this->record[$field])) if($this->record[$field] instanceof \Organization)  return $this->record[$field];
				$this->record[$field] = $system->organizations->RetrieveById($this->record[$field]);
				break;	
			case self::ORG_ID:
				if(isset($this->record[$field])) return $this->record[$field]; 
				break;
		}

		if( in_array($field,$this->boolean_fields)) {
			$value = isset($this->record[$field])? (($this->record[$field]) ? 't' : 'f') : null;   	
		}
		if( in_array($field,$this->private_fields)) return null;
 		return isset($this->record[$field]) ? $this->record[$field] : null; 
 	}
 	
 	public function __set($field,$value) {
 		if(gettype($field)!=='string') return;
 		if(in_array($field,$this->readOnly_fields)) throw new \Exception('Invalid parameter access:'.get_class($this).' attempt access of field in readOnly_fields array; ($field)');
		
		if(!isset($this->table)) return;
	
		if( $value instanceof CachedRecord ) {
			$value = $value->id;
		} elseif( is_object($value) ) {
			if(isset($value->id)) $value= $value->id;
		} elseif( is_bool($value)) {
			$value = ($value) ? 't' : 'f';
		} elseif( !(is_string($value) or is_numeric($value))) {
			throw new \Exception('Setting invalid value for record:'.get_class($this)." attempting to set $field to unsupported type ". gettype($value));
		}
		
		$query = "UPDATE {$this->table} SET $field=?".($this->hasModified) ? ",modified=now()" : '';
		$query.= "WHERE {$this->idField}=?";
		$result = $system->db->Execute($query,array($this[$this->idField]) );		
	}
		
	public function ToXMLNode($nodeName='record') {

		if(!isset($this->record)) return "";

		$node = '<%s %s />';
		$attributes = '';
		
		foreach( $this->record as $attribute=>$value) {
		
			if(!in_array($attribute,$this->private_fields)) {
				$object = $this->$attribute;
				if($attribute != 'id') {
					if( $object instanceof CachedRecord ) $object  = $object->ToString();
					if(!(is_string($object) or is_numeric($object))) $object = $value;
				}
				
				$attributes.=sprintf('%s="%s" ',$attribute,$object);				
			}
		}
		return sprintf($node,$nodeName,$attributes);
	}
	
	public function ToString( ) {
		if(!is_null($this->nameField)  ) return $this->record[$this->nameField];
		else return $this->id;
	}
	
	public function GetMetadataAttributes() {
	  $fields = array();
      $system = \System::Get();
      // fetch a recordset, then iterate thru its fields, populating the $attributes
      $fieldtypes = array( 'C'=>'text_input', 'X'=>'text_area', 'D'=>'date',
                           'T'=>'time', 'B'=>'text_area',
                           'N'=>'float',
                           'I'=>'int', 'R'=>'int',
                           'L'=>'boolean'
                         );

	  $rs = $system->db->Execute("SELECT * FROM {$this->table} LIMIT 1");
	
      for ($i=0; $i<$rs->FieldCount(); $i++) {
         $field     = $rs->FetchField($i);
         $fieldname = $field->name;
     	 if( in_array($field,$this->private_fields)) { continue; }
         $fieldtype = $fieldtypes[$rs->MetaType($field->type)];
         $fields[] = "$fieldname:$fieldtype";
         
      }
      return 'fields="'.implode(",",$fields).'"';
   }

		
		
	public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }
    
    public function offsetExists($offset) {
        return isset($this->$offset);
    }
    
    public function offsetUnset($offset) {
        unset($this->$offset);
    }
    
    public function offsetGet($offset) {
        return $this->$offset;
    }
    
    public function GetRecord() {
        return $this->record;
    }
}
?>
