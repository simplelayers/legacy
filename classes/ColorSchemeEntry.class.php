<?php

/**
 * A ColorSchemeEntry object represent a single entry in a color scheme. An "entry" is also called
 * a "rule" or a "classification" or a "class" but the term "color scheme entry" is used to avoid confusion with
 * the programming terem "class"
 *
 * This same class is used to represent an entry in a Layer's default color scheme, and also in
 * the color scheme that's used by the Layer when viewed within a Project.
 *
 * Public attriibutes:
 * - id -- The entry's unique ID#. Read-only.
 * - scheme -- A link to the ColorScheme object that this entry is part of. Read-only.
 * - layer -- A link to the Layer object that's using the color scheme. Only for Layer default schemes.
 * - projectlayer -- A link to the ProjectLayer object that's using the color scheme. Only for ProjectLayer schemes.
 * - priority -- The entry's priority; entries are processed from lowest to highest priority to find the first match.
 * - criteria1 -- The 3-part criteria for a feature to match this entry: the column to compare.
 * - criteria2 -- The 3-part criteria for a feature to match this entry: the operator for comparison.
 * - criteria3 -- The 3-part criteria for a feature to match this entry: the value to compare against.
 * - fill_color -- The color used for the feature's fill, in HTML format, e.g. #FFFFFF
 * - stroke_color -- The color used for the feature's stroke/outline, in HTML format, e.g. #000000
 * - description -- A text description for this entry.
 * - symbol -- String, which symbol to use for drawing these features? This is a fill type for polygons,
 * a stroke type for lines, and a point symbol for points.
 * - symbol_size - An integer indicating the size of the symbol. This corresponds to one of the SYMBOLSIZE_*
 * constants.
 *
 * @package ClassHierarchy
 */
class ColorSchemeEntry {
	/**
	 * @ignore
	 */
	private $world; // a link to the World we live in
	/**
	 * @ignore
	 */
	public $id; // the uniqiue ID# for this color scheme entry
	/**
	 * @ignore
	 */
	public $scheme; // a link to the ColorScheme we're part of
	/**
	 * @ignore
	 */
	private $table; // which table we're supposed to use, passed from the ColorScheme
	// for layer default colors, the layer_default_colors table is used;
	// for projectlayer colors, the project_layer_colors table is used
	// see also the $idfield definition below.
	/**
	 * @ignore
	 */
	private $idfield; // which idfield we're supposed to use, passed from the ColorScheme
	// for layer default colors, the layer field identifies which layer we're working with
	// for projectlayer colors, the projectlayer field identifies which projectlayer we're working with
	// see also the $table definition above.
	
	
	private $record = null;
	
	/**
	 * @ignore
	 */
	function __construct( &$world, &$scheme, $id, $table, $idfield) {
		$this->world = $world;
		$this->scheme = $scheme;
		$this->id = $id;
		$this->table = $table;
		$this->idfield = $idfield;
	}
	
	///// make attributes directly fetchable and editable
	/**
	 * @ignore
	 */
	function __get($name) {
	    $db= System::GetDB(System::DB_ACCOUNT_SU);
	    
	    if(is_null($this->record)) {
	        $this->record = $db->GetRow( "SELECT * FROM {$this->table} WHERE id=?", array ($this->id ) );
	    
	    }
		
		// simple sanity check
		if (preg_match ( '/\W/', $name ))
			return false;
		
		// if they asked for the Layer/ProjectLayer, hand it back
		if ($name == $this->idfield)
			return $this->scheme->scheme;
		
		
		$realName = $name;
		// if we got here, it must be a direct attribute
		if($name=='label_style_string') {
		    $name = 'label_style';
		}
		$value = isset($this->record[$name]) ? $this->record[$name] : null;
		
		
		if($realName == 'label_style_string') {
		    return $value;
		}
		if($name == 'label_style') {
		    if( is_null($value)) return null;
		    if(substr($value,0,1) != '{') {
		        $value="{".$value;
		        $value.="}";
		    }
		    $value = json_decode($value,true);
		    
		}
		
		
		if (! $value)
			return null;
		//$value = $value->fields['value'];
		// if it's a hex color, ensure that it has the # in front
		if ($name == 'fill_color' or $name == 'stroke_color') {
			if ($value != 'trans' and substr ( $value, 0, 1 ) != '#')
				
				$value = '#' . str_pad($value,6,'0',STR_PAD_LEFT);
		}
		// return the value, whatever it was
		return $value;
	}
	/**
	 * @ignore
	 */
	function __set($name, $value) {
		 $db= System::GetDB(System::DB_ACCOUNT_SU);
	   // simple sanity check
		if (preg_match ( '/\W/', $name ))
			return false;
		
		// a few items cannot be set
		if ($name == 'id')
			return false;
		if ($name == 'scheme')
			return false;
		if ($name == $this->idfield)
			return false;
		
		
		$nullable = false;
		if($name=="label_style") {
		    $nullable = true;
		}
		
		// sanitize text fields
		if (is_null($value)) {
		    if(!$nullable ) return false;
		}
		
		// the priority is a bit funny, in that we want to swap priority numbers with whatever else has this
		// same priority
		if ($name == 'priority') {
			//$old = $this->priority;
			System::GetDB()->Execute ( "UPDATE {$this->table} SET priority=? WHERE id=?", array ($value, $this->id ) );
			//System::GetDB()->Execute ( "UPDATE {$this->table} SET priority=? WHERE priority=? AND id!=?", array ($old, $value, $this->id ) );
			$this->scheme->sortPriorities ();
			return;
		}
		
		// if we got here, we must be setting a direct attribute
		$params = array ($value, $this->id );
		
		System::GetDB()->Execute ( "UPDATE {$this->table} SET {$name}=? WHERE id=?", $params );
		// if we got here, we know we'll be doing a change, so go ahead and set the last_modified stamp
		$this->touch ();
		
		$this->record = $db->GetRow( "SELECT * FROM {$this->table} WHERE id=?", array ($this->id ) );
	}
	function MergeEntry($entry) {
	    $properties = array('priority','criteria1','criteria2','criteria3','fill_color','stroke_color','description','symbol','symbol_size','label_style');
	    foreach($properties as $property) {
	        if(is_array($entry)) {
	            $this->$property = $entry[$property];
	        } else {
	           $this->$property = $entry->$property;
	        }
	    }	    
	}
	function RecordMatches($record) {
	    switch($this->criteria2) {
	        case Comparisons::COMPARE_NONE:
	            return true;
	        case Comparisons::COMPARE_EQUALS:
	            return ($record[$this->criteria1] == $this->criteria3);
	        case Comparisons::COMPARE_NOT_EQUALS :	
	           return  ($record[$this->criteria1] != $this->criteria3);
	        case Comparisons::COMPARE_GT:
	            return ($record[$this->criteria1] > $this->criteria3);
	        case Comparisons::COMPARE_GT_OR_EQUAL:
	            return ($record[$this->criteria1] >= $this->criteria3);
	        case Comparisons::COMPARE_LT:
	            return ($record[$this->criteria1] < $this->criteria3);
	        case Comparisons::COMPARE_LT_OR_EQUAL:
	            return ($record[$this->criteria1] <= $this->criteria3);
	        case Comparisons::COMPARE_HAS:
	           return stripos(strtolower($this->criteria3),strtolower($record[$this->criteria1]) >=0);
	        case Comparisons::COMPARE_NOT_HAS:
	           return stripos(strtolower($this->criteria3),strtolower($record[$this->criteria1]) <0);
	        case Comparisons::COMPARE_ISNULL:
	            return is_null($record[$this->criteria1]);
	        case Comparisons::COMPARE_ISNAN:
	            return ($record[$this->criteria1] === NAN);
	        case Comparisons::COMPARE_NOT_ISNULL:
	             return !is_null($record[$this->criteria1]);
	        case Comparisons::COMPARE_IN:
	           $c3 = explode(',', $this->criteria3);
	           return in_array($record[$this->criteria1],$c3);	           
	        case Comparisons::COMPARE_NOT_IN:
	           return !in_array($record[$this->criteria1],$c3);
	        case Comparisons::COMPARE_STARTS:
	            $c3len = strlen($this->criteria3);
	            $c1len = strlen($this->criteria1);
	            if($c3len > $c1len) return false;
	            $c1 = strtolower($this->criteria1);
	            $c3 = strtolower($this->criteria3);
	           return (substr($c1,0,$c3len)==$c3);
	        case Comparisons::COMPARE_ENDS:
	           $c3len = strlen($this->criteria3);
	           $c1len = strlen($this->criteria1);
	           if($c3len > $c1len) return false;
	           $c1 = strtolower($this->criteria1);
	           $c3 = strtolower($this->criteria3);
	           return (substr($c1,-$c3len) == $c3);
	    }
	}
	
	/**
	 * If this scheme entry is modified, flag the Layer or ProjectLayer as being modified.
	 */
	function touch() {
		$this->scheme->parent->touch ();
	}
	
	/////
	///// as usual, the self-destruct button to delete this entry
	/////
	/**
	 * The "self-destruct button", to delete the color scheme entry.
	 */
	function delete() {
		$this->touch ();
		$this->world->db->Execute ( "DELETE FROM {$this->table} WHERE id=?", array ($this->id ) );
		$this->scheme->sortPriorities ();
	}
	
	function toXML() {
	    $labelStyle = htmlentities($this->label_style_string);
		$description = htmlentities($this->description);
		$rule = <<< COLORRULE
<rule crid="{$this->id}" description="$description" priority="{$this->priority}" fill="{$this->fill_color}" stroke="{$this->stroke_color}" field="{$this->criteria1}" operator="{$this->criteria2}" value="{$this->criteria3}" symbol="{$this->symbol}" size="{$this->symbol_size}" label_style="$labelStyle"/>
COLORRULE;
		return $rule;
	}
	
	function toAssocArray() {
		return array ('crid' => $this->id, 'priority' => $this->priority, 'fill' => $this->fill_color, 'stroke' => $this->stroke_color, 'field' => $this->criteria1, 'operator' => $this->criteria2, 'value' => $this->criteria3, 'symbol' => $this->symbol, 'size' => $this->symbol_size );
	}
	
	function toGIDQuery($layerID) {
		$where = Comparisons::ToQueryWhere($this->criteria1,$this->criteria2,$this->criteria3);
		$query = "select array_to_string(array(select gid FROM vectordata_{$layerID} $where),',') as gids";
		return $query;	
	}

}

?>
