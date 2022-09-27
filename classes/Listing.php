<?php
class Listing
implements ArrayAccess
{
	private $list;
	public function __construct() {
		$args = func_get_args();
		if(sizeof($args==1)) { if(gettype($args[0])=='array')$args = $args[0];}
		$this->list = $args;
	}

	public function __get($item) {
		$result = isset($this->list[$item]) ? $this->list[$item] : null;
		if(is_null($result) ) $result = (in_array($item,$this->list)) ? $item : null;
		return $result;
	}

	public function ToHTMLOptions() {
		$tag = '<option value="%s">%s</option>';

		$options = '';

		foreach($this->list as $key=>$val) {
			if(is_integer($key)) $key=$val;
			$options.= sprintf($tag,$val,$key);
		}
		return $options;
	}

	public function ToXMLNodes($nodeName='item' ) {
		$node = '<%s label="%s" value="%s" />';
		$nodes = '';
		foreach($this->list as $key=>$val) {
			if(is_integer($key)) $key=$val;
			$nodes.= sprintf($node,$nodeName,$val,$key);
		}
		return $nodes;
	}

	public function ToString($separator=',') {
		return implode($separator,$this->list);
	}

	// ArrayAccess interface functions, allows object instance to be accessed like an array.

	public function offsetSet($offset,$value) {
		throw new Exception('Listing access error:'.get_class($this).' - listing values are read-only');
	}

	public function offsetExists($offset) {
		if(!is_string($offset)) throw new Exception('Listing access error:'.get_class($this).' - only supports string indexes');
		$result = is_null($this->listing[$offset]) || in_array($offset,$this->list);

	}

	public function offsetUnset($offset) {
		throw new Exception('Listing access error:'.get_class($this).' - listing values are read-only');
	}

	public function offsetGet($offset) {
		if(!is_string($offset)) throw new Exception('Listing access error:'.get_class($this).' - only supports string indexes');
		$result = isset($this->list[$offset]) ? $this->list[$offset] : null;
		if(is_null($result) ) $result = (in_array($offset,$this->list)) ? $offset : null;
		return $result;
	}

}
?>