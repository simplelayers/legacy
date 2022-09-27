<?php
/**
 * Enumeration classes
 * These classes allow for establishing relatively simple enumeration
 * in an object-oriented setup. They should allow for easy translation
 * between enumerated Labels and their values.
 */
/**
 * The Enum class imlements ArrayAccess so that rather than using the
 * object property operator -> you use bracket syntax ['item name'] or
 * [2] to refer to enumerated items or their associated values. Specifying
 * a string name will return the associated value. Specifying a value will
 * return the associated label. String names are case insenstive.
 * 
 * Examples:
 * $access = new Enum('None','Read',"Write",'Admin');
 * $accessLevel = 3;
 * $accessLabel = $access[$accessLevel];//accessLabel now is "Admin";
 * $accessLevel = $access['none'];//accessLevel now is 0;
 * 
 */
class Enum
implements ArrayAccess
{
	protected $labels;
	protected $keys;
	protected $values;
	
	public function __construct() {
		$this->labels = array();
		$this->keys = array();
		$this->values = array();

		$data = func_get_args();

		if(sizeof($data)==0) return;// throw new Exception('Invalid Enum:Enum constructed without data');
		if(sizeof($data)==1) {
			if(gettype($data)=='array') {
				foreach($data[0] as $key=>$val) {
					$label = is_numeric($key) ? $val : $key;
					$value = is_numeric($val) ? $val : $key;
					
					if(!is_numeric($value)) throw new Exception("Invalid Enum:Enum constructed with non numeric content $key=>$val");
					if(stripos($value,'.')>-1) $value = floatval($value);
					array_push($this->labels,$label);
					array_push($this->keys, strtolower($label));
					array_push($this->values, $value);
				}
			}	
		} else {
			$i = 0;
			foreach($data as $entry ) {
				$label = $entry;
				$value = $i;
				array_push($this->labels,$label);
				array_push($this->keys, strtolower($label));
				array_push($this->values,$value);
				$i++; 
			}
		}
		
	}
	
	public function IsItem($item) {
		return isset($this[$item]); 
	}

	public function ItemCount() {
		if($this->IsItem(0)) {
			return count($this->values) -1;
		}
		return count($this->values);
	}
	
	public function ToOptionAssoc($revKeyVal=false) {
		$assoc = array();
		foreach ($this->keys as $key) {
			if($revKeyVal) {
				$assoc[$key] = $this[$key];
			} else { 
				$assoc[$this[$key]] = $key;
			}
		}
		return $assoc;
	}
	
	public function ToJSObj($varname) {
		return 'var '.$varname.' = '.json_encode($this->ToOptionAssoc());
	}
	
	
	public function AddItem( $key, $value=null){
		if(!in_array($key,$this->keys)) {
			array_push($this->labels,$key);
			array_push($this->keys,strtolower($key));
			$value = is_null($value) ? count($this->values) : $value;
			array_push($this->values,$value);
		} elseif(!is_null($value)) {
			$i = array_search($key,$this->keys);
			$this->values[$i] = $value;
			
		}
	}
	
	public function ToHTMLOptions($includeEmpty=false) {
		$string = "";
		foreach($this->labels as $label) {
			$value = $this[$label];
			if(!$includeEmpty && ($label=='')) continue;
			$string.= "<option value='$value' >$label</option>\n";
		}
		return $string;
	}
	
	public function offsetSet($offset, $newValue) {
   		throw new Exception('Invalid Enum operation:enumerated values are read only.');
	}
    
  	public function offsetExists($offset) {
     	if(is_numeric($offset)) {
			foreach($this->values as $value) {
				if( $value == $offset ) return true;
			}
			return false;
		}
		
		$offset = strtolower($offset);
		
		foreach($this->keys as $key)
		{
			if($key == $offset) return true;
		}
		return false;
		
   	 }
    
  	 public function offsetUnset($offset) {
      	 	throw new Exception('Invalid Enum operation:Enum values are read only.');
   	 }
    
  	 public function offsetGet($offset) {
       	$i = 0;
		if(is_numeric($offset) ) {
			foreach($this->values as $value ) {
				if($value == $offset) return $this->labels[$i];
				$i++;
			}
			return null;
		}
		$offset = strtolower($offset);
		$i = 0;
		foreach($this->keys as $key ) {
			if($key == $offset) return $this->values[$i];
			$i++;
		}
		return null;
   	 }
   	 
   	 public function __get($target) {
   	 	if($this->offsetExists($target)) return $this->offsetGet($target);
   	 }
   	 
   	 public function __set($target,$val ) {
   	 	$this->offsetSet($target,$val);
   	 }
   	 
   	 
}






?>
