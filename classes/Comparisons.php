<?php

class Comparisons {
	const COMPARESTR_NONE = '';
	const COMPARESTR_EQUALS = 'equals';
	const COMPARESTR_EXACTLY_EQUALS = 'exact match';
	const COMPARESTR_NOT_EQUALS = 'does not equal';
	const COMPARESTR_GT = 'is greater than';
	const COMPARESTR_GT_OR_EQUAL = 'is greater than or equal to';
	const COMPARESTR_LT = 'is less than';
	const COMPARESTR_LT_OR_EQUAL = 'is less than or equal to';
	const COMPARESTR_HAS = 'contains';
	const COMPARESTR_ISNULL = 'is null or empty';
	const COMPARESTR_NOT_ISNULL = 'is not null or empty';
	const COMPARESTR_IN = 'is in list';
	const COMPARESTR_NOT_IN = 'is not in list';
	
	
	const COMPARE_NONE = '';
	const COMPARE_EQUALS = '==';
	const COMPARE_EXACTLY_EQUALS = '===';
	const COMPARE_NOT_EQUALS = '<>';	
	const COMPARE_GT = '>';
	const COMPARE_GT_OR_EQUAL = '>=';
	const COMPARE_LT = '<';
	const COMPARE_LT_OR_EQUAL = '<=';
	const COMPARE_HAS = 'contains';
	const COMPARE_NOT_HAS = '!contains';
	const COMPARE_ISNULL = 'isnull';
	const COMPARE_ISNAN = 'isnan';
	const COMPARE_NOT_ISNULL = 'not_isnull';
	const COMPARE_IN = 'IN';
	const COMPARE_NOT_IN = 'NOT IN';
	const COMPARE_STARTS =  'starts';
	const COMPARE_ENDS = 'ends';
	
	const OPERATOR_AND = ' AND ';
	const OPERATOR_OR = ' OR ';
	
	const OPERATOR_MONGO_AND = '$and';
	const OPERATOR_MONGO_OR = '$or';
	
	private static $enum= null;
	
	public static function GetEnum($replace=false) {
		if( (self::$enum !==NULL) and !$replace ) return self::$enum;
		self::$enum = new \Enum(array());
		self::$enum->AddItem(self::COMPARESTR_NONE,self::COMPARE_NONE);
		self::$enum->AddItem(self::COMPARESTR_EQUALS,self::COMPARE_EQUALS);
		self::$enum->AddItem(self::COMPARESTR_EXACTLY_EQUALS,self::COMPARE_EXACTLY_EQUALS);
		self::$enum->AddItem(self::COMPARESTR_NOT_EQUALS,self::COMPARE_NOT_EQUALS);
		self::$enum->AddItem(self::COMPARESTR_GT,self::COMPARE_GT);
		self::$enum->AddItem(self::COMPARESTR_GT_OR_EQUAL,self::COMPARE_GT_OR_EQUAL);
		self::$enum->AddItem(self::COMPARESTR_LT,self::COMPARE_LT);
		self::$enum->AddItem(self::COMPARESTR_LT_OR_EQUAL,self::COMPARE_LT_OR_EQUAL);
		self::$enum->AddItem(self::COMPARESTR_HAS,self::COMPARE_HAS);
		self::$enum->AddItem(self::COMPARESTR_ISNULL,self::COMPARE_ISNULL);
		self::$enum->AddItem(self::COMPARESTR_NOT_ISNULL,self::COMPARE_NOT_ISNULL);
		
		return self::$enum;
	}
	
	static $comparisons = array(
			self::COMPARE_NONE => self::COMPARESTR_NONE,
			self::COMPARE_EQUALS => self::COMPARESTR_EQUALS,
	       self::COMPARE_EXACTLY_EQUALS => self::COMPARESTR_EXACTLY_EQUALS,
	     	self::COMPARE_GT  => self::COMPARESTR_GT,
			self::COMPARE_GT_OR_EQUAL => self::COMPARESTR_GT_OR_EQUAL,
			self::COMPARE_LT  => self::COMPARESTR_LT,
			self::COMPARE_LT_OR_EQUAL => self::COMPARESTR_LT_OR_EQUAL,
			self::COMPARE_HAS => self::COMPARESTR_HAS,
			self::COMPARE_ISNULL => self::COMPARESTR_ISNULL,
	);

	static $comparisons_i18n = array(
		self::COMPARE_NONE =>'sl_comparestr_none_lbl',
		self::COMPARE_EQUALS =>'sl_comparestr_equals_lbl',
	    self::COMPARE_EXACTLY_EQUALS =>'sl_comparestr_exactly_equals_lbl',
	    self::COMPARE_GT  => 'sl_comparestr_gt_lbl',
		self::COMPARE_GT_OR_EQUAL => 'sl_comparestr_gt_or_equal_lbl',
		self::COMPARE_LT  => 'sl_comparestr_lt_lbl',
		self::COMPARE_LT_OR_EQUAL => 'sl_compare_lt_or_equal_lbl',
		self::COMPARE_HAS => 'sl_compare_contains_lbl',
		self::COMPARE_ISNULL => 'sl_compare_isnull_lbl',
		self::COMPARE_NOT_ISNULL => 'sl_compare_not_isnull_lbl',
			
	);
	
	static function ToHTMLOptions() {
		$string = "";
		foreach(self::$comparisons as $value=>$label) {
			$string.= "<option value='$value' >$label</option>\n";							
		}		
		return $string;
	}
	
	
	public static function GroupMongoCriteria($criteria,$operator) {
		$operator = ($operator== self::OPERATOR_AND) ? self::OPERATOR_MONGO_AND : $operator;
		$operator = ($operator== self::OPERATOR_OR) ? self::OPERATOR_MONGO_OR : $operator;
		if(!in_array($operator,array(self::OPERATOR_MONGO_AND,self::OPERATOR_MONGO_OR))) return $criteria;
		return array($operator=>$criteria);
		
	}
	
	public static function ToMongoCriteria($field,$comparison,$val=null,$modifier=null,$incWhere=null) {
		$criteria = array();
		switch($comparison) {
			case self::COMPARE_NONE:
				return nll;
			case self::COMPARE_EQUALS:
				$criteria[$field]=$val;
				break;
			case self::COMPARE_NOT_EQUALS:
				$criteria[$field]=array('$ne'=>$val);
				brek;
			case self::COMPARE_GT:
				$criteria[$field]=array('$gt'=>$val);
				break;
			case self::COMPARE_GT_OR_EQUAL:
				$criteria[$field]=array('$gte'=>$val);
				break;
			case self::COMPARE_LT:
				$criteria[$field]=array('$lt'=>$val);
				break;
			case self::COMPARE_LT_OR_EQUAL:
				$criteria[$field]=array('$lte'=>$val);
				break;
			case self::COMPARE_HAS:
				$criteria[$field]=new \MongoRegex( "/". $val ."/mi" );
				break;
			case self::COMPARE_NOT_HAS:
				$criteria[$field]=array('$nin',new \MongoRegex( "/"));
				break;
				case self::COMPARE_NOT_HAS:
					$criteria[$field]=array('$nin',new \MongoRegex( "/"));
					break;
			case self::COMPARE_ISNULL:
				$criteria[$field]=null;
				break;
			case self::COMPARE_NOT_ISNULL:
				$criteria[$field]=array('$ne',null);				
			case self::COMPARE_IN:
				if(!is_array($val)) $val = array($val);
				$criteria[$field]=array('$in'=>$val);
				break;
			case self::COMPARE_NOT_IN:
				if(!is_array($val)) $val = array($val);
				$criteria[$field]['$nin']=$val;
				break;
		}
		
		return $criteria;
		
	}
	
	public static function ToQueryWhere($field,$comparison,$val=null,$modifier=null, $incWhere=true,$negate=false) {
	    
	   $toUpper = $modifier == "upper";
		$toLower = $modifier == "lower";
		if($modifier == '') $modifier = null;
		$hasModifier = !is_null($modifier);
		
		if($hasModifier) {
			
			$modifier = explode(':',$modifier);
			if($modifier['0']=='substr') {
				array_shift($modifier);
				$from = array_shift($modifier);
				$to = count($modifier) ? array_shift($modifier) : null;
				$field = is_null($to) ? "substr($field,$from)" : "substr($field,$from,$to)";					
				$hasModifier = false;
				$modifier = null;
			}
			
			
		}
		
	
		
		
		$comparison = trim($comparison);
		
		$where = $incWhere ? "WHERE " : "";
		
		if( $negate) {
			$where.='NOT ';
		}
		
		switch($comparison) {
			case self::COMPARE_NONE:
				return '';
			case self::COMPARE_EXACTLY_EQUALS:
			case self::COMPARE_EQUALS:
			    			
			    if($comparison == self::COMPARE_EXACTLY_EQUALS) {
			        $hasModifier = false;			        
			    }	
			    $val = System::GetDB()->qstr($val,get_magic_quotes_gpc());
			    $field = "coalesce({$field}::text,'')";
				if(!$hasModifier) return $where."$field = $val";
				
				if($toUpper) return $where."upper($field) = upper($val)";
				if($toLower) return $where."lower($field) = lower($val)";				
				break;
			case self::COMPARE_GT:
			case self::COMPARE_GT_OR_EQUAL:
			case self::COMPARE_LT:
			case self::COMPARE_LT_OR_EQUAL:
			case self::COMPARE_NOT_EQUALS:
			    $val = System::GetDB()->qstr($val,get_magic_quotes_gpc());
			    
				if(!$hasModifier) {
					$where."$field $comparison $val";
					$where.= "$field $comparison $val";
					return $where;
				}
				$field = "coalesce($field,'')";
				if($toUpper) return $where."upper($field) $comparison ".strtoupper($val);
				if($toLower) return $where."lower($field) $comparison ".strtolower($val);
			     break;		
			case self::COMPARE_HAS:
				$val = "'%{$val}%'";
				
				if(!$hasModifier) {
				    $field = "coalesce($field::text,'')";
				   return  $where.="lower($field) LIKE ".strtolower($val);
				}
				
				if($toUpper) $where.="upper(coalesce($field,''::text)) LIKE ".strtoupper($val);
				if($toLower) $where.="lower(coalesce($field::text,'')) LIKE ".strtolower($val);
				
				return $where;	
				break;				
			case self::COMPARE_STARTS:
				$val = "'{$val}%'";
				if(!$hasModifier) {
				    $field = "coalesce({$field}::text,'')";
					return $where."lower({$field}::text) LIKE ".strtolower($val);
				}
				if($toUpper) return $where."upper(coalesce($field::text,'')) LIKE ".strtoupper($val);
				if($toLower) return $where."lower(coalesce($field::text,'')) LIKE ".strtolower($val);
				break;
			case self::COMPARE_ENDS:
				$val = "'%{$val}'";;
				if(!$hasModifier) {
				    $field = "coalesce({$field}::text,'')";
					return $where."lower($field) LIKE ".strtolower($val);
				}
				
				if($toUpper) return $where."upper(coalesce($field::text,'')) LIKE ".strtoupper($val);
				if($toLower) return $where."lower(coalesce($field::text,'')) LIKE ".strtolower($val);
				break;
						
			case self::COMPARE_NOT_HAS:
				if(stripos("$val",'%') === false ) $val = "%$val%";
				$val = "'$val'";
				if(!$hasModifier) {
				    $field = "coalesce({$field}::text,'')";
					return $where."lower($field) NOT LIKE ".strtolower($val);
				}				
				if($toUpper) return $where."upper(coalesce($field::text,'')) LIKE ".strtoupper($val);
				if($toLower) return $where."upper(coalesce($field::text,'')) LIKE ".strtolower($val);
				break;
			case self::COMPARE_ISNAN;
			case self::COMPARE_ISNULL;
			    return $where."$field IS NULL";
			case self::COMPARE_NOT_ISNULL;
				return $where."$field IS NOT NULL";
			case self::COMPARE_IN;
				if(!$hasModifier) return $where."$field IN ($val)";
				$field = "coalesce($field,'')";
				if($toUpper) return $where."upper(coalesce($field::text,'')) IN (".strtoupper($val).")";
				if($toLower) return $where."lower(coalesce($field::text,'')) IN (".strtolower($val).")";	
				break;			
			case self::COMPARE_NOT_IN;
				if(!$hasModifier) return $where."$field NOT IN ($val)";
				$field = "coalesce($field,'')";
				if($toUpper) return $where."upper(coalsece($field,'')::text) NOT IN(".strtoupper($val).")";
				if($toLower) return $where."lower(coalesce($field::text,'')) NOT IN(".strtolower($val).")";
				break;				
		}
		
	}
	
}

?>