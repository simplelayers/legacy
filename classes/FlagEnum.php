<?php
/**
 * A FlagEnum is an enumeration where each item gets a value that is a
 * power of 2 and may be used with bit-wise comparisons.
 *
 * It provides an IsFlagged function for determining whether a value
 * has a bit set for the target flagged item.
 *
 * Example:
 * $features = new FlagEnum('Map','Layers List','Navigation');
 * $prefs = $features['map'] + $features['Navigation'];
 * $useLayersList = $features->IsFlagged('Layers List',$prefs); //= false.
 * $userNavigation = $features->IsFlagged('navigation',$prefs); //= true.
 *
 * @see Enum
 */
class FlagEnum
extends Enum
{
	private $hasNoValue = false;
	public function __construct()
	{
		$this->keys = array();
		$this->labels= array();
		$this->values = array();
		$data = func_get_args();

		if(sizeof($data) == 1) {				
			if(gettype($data) == 'array') $data = $data[0];
		}
		
		$i=0;
		foreach($data as $label) {
			if(is_numeric($label)) throw new Exception('Invalid Enumeration:enumerated items may not be numeric.');
			array_push($this->labels,$label);
			array_push($this->keys,strtolower($label));
			array_push($this->values,pow(2,$i));
			$i++;
		}
	}

	public function AddItem( $key, $value=null){
		if($value === 0) $this->hasNoValue = true;
		
		array_push($this->labels,$key);
		array_push($this->keys,strtolower($key));
		$value = is_null($value) ? pow(2,count($this->values)) : $value;
		array_push($this->values,$value);
		return $value;
	}

	public function ToHTMLOptions($includeEmpty=false) {
		$tag = '<option value="%s">%s</option>';

		$options = '';
			

		for($i=0; $i< count($this->values);$i++ ) {
			$options.= sprintf($tag,$this->values[$i],$this->labels[$i]);
		}
		return $options;
	}

	public function ToXMLNodes($nodeName='item' ) {
		$node = '<%s label="" value="" />';
		$nodes = '';
		for($i=0; $i< count($this->values);$i++) {
			$nodes.= sprintf($node,$nodeName,$this->labels[$i],$this->values[$i]);
		}
		return $nodes;
	}

	public function IsFlagged($key,$value) {
		if(is_numeric($key)) {$keyValue = $key;} 
		else {$keyValue = $this[$key];}
		return (($keyValue & $value)==$keyValue);
	}
	
	

	public function GetMaxValue() {
	
		$sub = ($this->hasNoValue ? 1 :0);
		
		return pow(2,count($this->values)-$sub )-1;
	}

}

?>
