<?php
/**
 * Pathifier is for working with heirarchical data flattened
 * into path-format.
 * 
 * Pathfier maintains an internal array with paths as keys to
 * corresponding values.
 * 
 * Pathifier implements the ArrayAccess interface so that you
 * may use bracket-syntax with the object instance to get or set
 * path values. 
 * 
 * Basic Usage:
 * // include/require the class
 * // assumes $path_to_classes is defined:
 * require_once($path_to_classes/Pathfinder.php');
 * 
 * $data = array('one'=>array('two'=>3,'four'=>array(5,6,7)),
 *				 'eight'=>array('nine'=>array('ten'=>11)));
 * 
 * $pathifier = new Pathifier();
 * $pathifier->fromArray($data);
 * 
 * Or, if you are starting with the same data in json format:
 * $json=<<<JSON
 * {"one":{"two":3,"four":[5,6,7]},"eight":{"nine":{"ten":11}}}
 * JSON;
 * $pathifier->fromJSON($json);
 * 
 * $ten = $pathifier['/eight/nine/ten'];
 * print($ten);
 * //output: 11
 * 
 * Note, paths start with '/' and do not have a trailing '/'
 * However, for array access the path is sanitized so:
 * $ten = $pathifier['eight/nine/ten'];
 * $ten = $pathifier['eight/nine/ten/'];
 * $ten = $pathifier['/eight/nine/ten'];
 * $ten = $pathifier['/eight/nine/ten/'];
 * 
 * are all evaluated as:
 * $ten = $pathifier['/eight/nine/ten'];
 * 
 * You may also set a path
 * $pathifier['/eight/nine/ten'] = 42;
 * $ten = $pathifier['/eight/nine/ten/'];
 * print($ten);
 * //output: 42 
 * 
 * And you may unset a path:
 * unset($pathifier['/eight/nine/ten'];
 * 
 * Working with output:
 * Arrays: If you want to work with the data as an unflatted array:
 * $dataArray = $pathifier->toArray();
 * print($dataArray['one']['two']);
 * // output: 3
 * 
 * JSON-unflat: If your intent is to add/change/update the pathifier
 * data to then make available to javascript or other JSON handlers:
 * $json = $pathifier->toJSON();
 *
 * Items
 * For front end support, sometimes it is easier to work with "items"
 * that is, a linear list of arrays/objects containing a name and value
 * property.
 * 
 * Instead of name and value properties, toItem provides an array with path and value properties.
 * $items = $pathifier->toItems();
 * print_r($items);
 * // output:
	Array
	(
	    [0] => Array
	        (
	            [path] => /one/two
	            [value] => 3
	        )
	
	    [1] => Array
	        (
	            [path] => /one/four/0
	            [value] => 5
	        )
	
	    [2] => Array
	        (
	            [path] => /one/four/1
	            [value] => 6
	        )
	
	    [3] => Array
	        (
	            [path] => /one/four/2
	            [value] => 7
	        )
	
	    [4] => Array
	        (
	            [path] => /eight/nine/ten
	            [value] => 11
	        )
	
	)
 * 
 * Or, for delivery to a browser:
 * $items = $pathifier->toItems(true);
 * print($items);
 * //output:
 * [{"path":"\/one\/two","value":3},{"path":"\/one\/four\/0","value":5},{"path":"\/one\/four\/1","value":6},{"path":"\/one\/four\/2","value":7},{"path":"\/eight\/nine\/ten","value":11}]
 * 
 * 
 * Pathifier also supports the Iterator interface which is used
 * to give foreach access to the path content:
 * 
 * foreach($pathifier as $path=>$value) {
 *		echo $path."\t".$value."\n";
 *	}
 *
 * // outputs:
 * /one/two	3
 * /one/four/0	5
 * /one/four/1	6
 * /one/four/2	7
 * /eight/nine/ten	11
 * 
 * @author Arthur Clifford
 *
 */
class Pathifier 
implements ArrayAccess,Iterator
{
	private $data;
	private $_cursor = 0;
	
	function Pathifier() {
		$this->data = array();
	}
	
	/**
	 * fromPaths takes an array where they keys are paths
	 * and uses that array as internal data.
	 * @param array $pathData
	 */
	function fromPaths($pathData) {
		$this->data = array();
		$this->data = $pathData;
	}
	
	/**
	 * fromArray takes a heirarchical array and flattens it into
	 * a path-based array to use as internal data.
	 * @param unknown $data
	 */
	function fromArray($data) {
		$this->data = array();
		$this->flattenArray($data, $this->data);		
	}
	
	/**
	 * Wrapper for fromArray, decodes json data source into
	 * a php array structure then calls fromArray to use
	 * the ultimately flattend array as internal data.
	 * @param unknown $json
	 */
	function fromJSON($json) {
		$this->fromArray(json_decode($json,true));
		
	}
	
	/**
	 * Return an unflattened array based on internal pathified data.
	 * @return array
	 */
	function toArray() {
		return $this->unflattenData($this->data);		
	}
	
	/**
	 * Wrapper for toArray; unflattens the inernal pathified data
	 * and returns it JSON encoded.
	 * @return string
	 */
	function toJSON() {
		return json_encode($this->toArray());
		
	}
	
	/**
	 * Returns a an array where each element is:
	 * array( 'path'=>...,'value'=>...)
	 * This is different than how the data is stored internally.
	 * 
	 * Returns an array or json string based on the asJSON parameter.	 
	 * 
	 * @param boolean $asJSON (optional) if true returns json, if false array is returned
	 * @return array|string
	 */
	function toItems( $asJSON = false) {
		$items = array();
		foreach($this->data as $path=>$value) {
			$items[] = array('path'=>$path,'value'=>$value);
		}
		if($asJSON) return json_encode($items);
		return $items;
	}
	
	/**
	 * Returns a linear array or json where each element is a path:
	 * 
	 *
	 * @param boolean $asJSON (optional) if true returns json, if false array is returned
	 * @return array|string
	 */
	function toPaths($asJSON) {
		if($asJSON ) {
			$json = array('paths'=>$this->data);
			return json_encode($json);
		}
		return $this->data;
		
		
	}
	
	/**
	 * Internfal function taht takes hierarchcal data and
	 * pathifies it.
	 * 
	 * @param array $data
	 * @param array $newArray
	 * @param string $oldKey
	 */
	private function flattenArray($data,&$newArray,$oldKey=null) {
		
		foreach( $data as $key=>$val ) {
			$nextKey=$oldKey.'/'.$key;
			if(!is_array($val) ) {
				$newArray[$nextKey] = $val;
				continue;
			}
			$this->flattenArray($data[$key],$newArray,$nextKey);
		}
	}
	
	/**
	 * Internal-function does the work of turning a flat array unto its unflat state.
	 * returns array
	 * @return array
	 */
	private function unflattenData( ) {
		$newArray = array();
		foreach($this->data as $path=>$value ) {
			$newArray = array_merge_recursive($newArray,$this->pathToArray($path,$value));
		}
		return $newArray;
	}
	
	/**
	 * Convert a path to a heirarchical array where the end poitn contains
	 * the specified value.
	 * 
	 * @param string $path
	 * @param mixed $value
	 * @return array
	 */
	private function pathToArray($path,$value,&$array=null) {
		$newArray = array();
		if(!is_null($array)) $newArray = array_merge($newArray,$array);
		$keys = explode("/",$path);
		array_shift($keys);
		$numKeys = count($keys);
	
		$syntax = "";
		foreach($keys as $key ){
			$syntax.='["'.$key.'"]';
		}
		eval('$newArray'.$syntax.'=$value;');
		return $newArray;
	
	}
	
	/**
	 * Internal function - resolves path-key to have a / at the beginning and no / at the end.
	 * 
	 * Returns the sanitized string.
	 * 
	 * @param string $offset
	 * @return string
	 */
	private function sanitizeOffset($offset) {
		if(substr($offset,0,1) != "/") $offset = "/$offset";
		if(substr($offset,-1,1) == "/") $offset = substr($offset,0,strlen($offset)-1);
		return $offset;
	}
	
	// ARRAY ACCESS Methods
	
	/**
	 * Part of ArrayAccess Interface
	 * Determines whether the key/offset being used exists.
	 * @param string offset
	 * @return bool true if offset exists, false otherwise
	 */
	public function offsetExists ($offset) {
		$offset = $this->sanitizeOffset($offset);
		return isset($this->data[$offset]);
	}
	
	/**
	 * Part of ArrayAccess Interface
	 * Returns the value represented by the key/path/offset
	 * @param offset string a path 
	 */
	public function offsetGet ($offset) {
		$offset = $this->sanitizeOffset($offset);
		return $this->data[$offset];		
	}
	
	/**
	 * Part of ArrayAccess Interface
	 * Sets a value for given key/path/offset
	 * @param offset string a path
	 * @param value mixed the value to set
	 */
	public function offsetSet ($offset, $value) {
		$offset = $this->sanitizeOffset($offset);
		return $this->data[$offset] = $value;
	}
	
	/**
	 * Part of ArrayAccess Interface
	 * Allows the unsetting of a key/path/offset
	 * @param offset string path
	 */
	public function offsetUnset ($offset) {
		$offset = $this->sanitizeOffset($offset);
		$this->data[$offset] = null;
		unset($this->data[$offset]);
	}

	// ITERATOR Interface functions	
	/**
	 * Part of Iterator Interface support
	 * Return the value for the current iteration.
	 * @return mixed the value for the current path.
	 */
	function current( ) {
		return $this->data[$this->key()];
	}
	
	/**
	 * Part of Iterator Interface support
	 * Return the path for the current iteration.
	 * @return string
	 */
	function key() {
		$keys = array_keys($this->data);
		return $keys[$this->_cursor];		
	} 				
	
	/**
	 * Part of Iterator Interface support
	 * Advance the internal iteration cursor to the next item/index.
	 * @return void
	 */
	function next( ) {
		$this->_cursor++;
	}
	
	/**
	 * Part of Iterator Interface support
	 * Rewind the internal iteration cursor tot he first item/index
	 * @return void
	 */
	function rewind( ) {
		$this->_cursor=0;
	}
	
	/**
	 * Part of Iterator Interface support
	 * The idea here is that there may not be a new key especially if we
	 * iterate higher than the number of items we have.
	 * @return boolean true if valid, false if not.
	 */
	function valid(  ) {
		if(count(array_keys($this->data))== $this->_cursor) return false;
		return $this->offsetExists($this->key());
	}
	
}

?>