<?php
namespace model;

class SL_Query {

    private $query = array();
    private $table = null;
    const LIKE_STARTS = 'starts';
    const LIKE_ENDS = 'ends';
    const LIKE_ANY = 'any';

    const LIKE_CASE_UPPER = 'upper';
    const LIKE_CASE_LOWER = 'lower';

    const STAT_MIN = 'min';
    const STAT_MAX = 'max';

    const QUERY_AND = 'and';
    const QUERY_OR = 'or';

    const ORDER_DESC = 'desc';
    const ORDER_ASC = 'asc';

    const COMPARE_NONE = '';
    const COMPARE_EQUALS = '==';
    const COMPARE_NOT_EQUALS = '<>';
    const COMPARE_GT = '>';
    const COMPARE_GT_OR_EQUAL = '>=';
    const COMPARE_LT = '<';
    const COMPARE_LT_OR_EQUAL = '<=';
    const COMPARE_HAS = 'contains';
    const COMPARE_NOT_HAS = '!contains';
    const COMPARE_ISNULL = 'isnull';
    const COMPARE_NOT_ISNULL = 'not_isnull';
    const COMPARE_IN = 'IN';
    const COMPARE_NOT_IN = 'NOT IN';

    private $groupsBegun = 0;
    private $groupsEnded = 0;


    private $numericFields = array();
    
    public function __construct() {

        
    }

    public function SetNumericFields($fields) {
        $this->numericFields = $fields;
    }
    public function NewTableQuery($table) {
        $this->query = array();
        $this->table = $table;
    }
    
    public function NewQuery(\Layer $layer) {
        $this->query = array();
        $this->query['layer'] = $layer->id;
        $this->table = $layer->url;
    }

    public function AddSort($field,$direction=null,$type=null) {
        if(!isset($this->query['sort'])) {
            $this->query['sort'] = array();
        }

        $sortInfo = array('field'=>$field);
        if(!is_null($type)) {
            $sortInfo['type'] = $type;
        }
        if($direction) $sortInfo['direction']=' '.$direction;
        $this->query['sort'][] = $sortInfo;
    }

    public function AddUpperSort($field,$direction=null,$type=null) {
        $this->AddSort('upper:'.$field,$direction=null,$type=null);
    }

    public function AddLowerSort($field,$direction=null,$type=null) {
        $this->AddSort('lower:'.$field,$direction=null,$type=null);
    }


    public function BeginCriteriaGroup($andOr=null) {
        $this->groupsBegun+=1;
        $criteria = array();
        if($andOr) $criteria['andor'] = strtolower($andOr);
        $criteria['group'] = $this->groupsBegun;
        $this->query['criteria'][] = $criteria;
    }

    public function EndCriteriaGroup() {
        $this->groupsEnded +=1;
        $criteria = array();
        $criteria['!group'] = $this->groupsEnded;
        $this->query['criteria'][] = $criteria;
    }


    public function GetSearchCriteria() {
        if($this->groupsBegun != $this->groupsEnded) {
            throw new \Exception('SLQuery::GetResults error: Groups begun does not mathc Groups ended.');
        }
        $obj = new \SearchCriteria($this->table,$this);
        $obj->numericFields = $this->numericFields;
        return $obj;
        
        
    }

    public static function GetValues($array,$field=null) {

        $values = array();
        foreach($array as $idx=>$item) {
            if(is_string($idx)) {
                return array($array);
            }
            if(is_null($field)) return $array;
            if(!isset($item[$field])) continue;
            $values[] = $item[$field];
        }
        return $values;
    }

    public static function GetKeyValues($array,$keyField,$valField=null) {
        $values = array();

        foreach($array as $idx=>$item) {
            	
            if(!is_numeric($idx)) {
                return self::GetKeyValues(array($array), $keyField,$valField);
            }
            $itemKey =isset($item[$keyField]) ?  $item[$keyField] : null;
            if(is_null($itemKey)) continue;
            if(is_null($valField)) {

                $values[$itemKey] = $item;

                continue;
            }
            	
            if(!isset($item[$keyField])) continue;
            $values[$item[$keyField]] = $item[$valField];
            	
        }

        return $values;
    }

    public static function GetUniqueValues($haystack,$field,$sortResult = true) {
        if(isset($haystack[$field])) return array($haystack[$field]);
        $values = array();
        foreach($haystack as $item) {
            if(!isset($item[$field])) continue;
            if(in_array($item[$field],$values)) continue;
            $values[] = $item[$field];
            	
        }
        sort($values);
        return $values;
    }


    public function AddDistinctField($field,$as=null,$dataType=null) {
        if(!isset($this->query['fields'])) {
            $this->query['fields'] = array();
        }
        $fieldData = array('field'=>$field,'distinct'=>1);
        if($as) $fieldData['as'] = $as;
        if($dataType) $fieldData['type']=$dataType;
        $this->query['fields'][] = $fieldData;
    }

    public function AddFields() {
        $fields = func_get_args();
        foreach($fields as $field) {
            $this->AddField($field);
        }
    }

    public function AddAllFields() {
        $this->AddField('*');
    }

    public function AddField($field,$as=null,$dataType=null) {
        if(!isset($this->query['fields'])) {
            $this->query['fields'] = array();
        }
        $fieldData = array('field'=>$field);
        if($as) $fieldData['as'] = $as;
        if($dataType) $fieldData['type'] = $dataType;
        $this->query['fields'][] = $fieldData;
    }

    public function AddUpperField($field,$as=null,$dataType=null) {
        $this->AddField($field,$as,'upper');
    }

    public function AddLowerField($field,$as=null,$dataType=null) {
        $this->AddField($field,$as,$dataType,'lower');
    }

    public function AddLimit($limit) {
        $this->query['limit'] = $limit;
    }

    public function AddStat($field,$type,$as) {
        if(!isset($this->query['stats'])) $this->query['stats'] = array();
        $this->query['stats'][] = array('field'=>$field,'stat'=>$type,'as'=>$as);
    }

    private function PrecheckCriteria() {
        if(!isset($this->query['criteria'])) {
            $this->query['criteria'] = array();
        }
    }

    public function AddIsNullCriteria($field,$andOr=null) {
        $this->PrecheckCriteria();
        $criteria = array('compare'=>self::COMPARE_ISNULL,'value'=>'');
        $this->AddCriteriaField($criteria,$field);
        if($andOr) $criteria['andor'] = strtolower($andOr);
        $this->query['criteria'][] = $criteria;
    }
    public function AddIsNotNullCriteria($field,$andOr=null) {
        $this->PrecheckCriteria();
        $criteria = array('compare'=>self::COMPARE_NOT_ISNULL,'value'=>'');
        $this->AddCriteriaField($criteria,$field);
        if($andOr) $criteria['andor'] = strtolower($andOr);
        $this->query['criteria'][] = $criteria;
    }

    public function AddInCriteria($field,$values,$quoteVals=true,$andOr=null) {

        $this->PrecheckCriteria();

        if($quoteVals) {
            $values = implode(',',array_map(create_function('$a','return "\'$a\'";'),$values));
        } else {
            $values =implode(';',$values);
        }

        $criteria = array('compare'=>self::COMPARE_IN,'value'=>$values);
        $this->AddCriteriaField($criteria,$field);
        if($andOr) $criteria['andor'] = strtolower($andOr);
        $this->query['criteria'][] = $criteria;
    }

    protected function AddCriteriaField(&$criteria,$field) {
        $fieldInfo = explode(':',$field);
        $field = array_pop($fieldInfo);

        if($field == 'substr') {
            	
            	
        }

        $criteria['field'] = $field;
        if(count($fieldInfo)) {
            $criteria['modifier'] = implode(':',$fieldInfo);
        }
    }


    public function AddLikeCriteria($field,$value,$pos='start',$andOr=null) {
        $this->PrecheckCriteria();

        switch($pos) {
            case 'starts':
            case 'start':
                $value.='%';
                break;
            case 'any':
                $value = '%'.$value.'%';
                break;
            case 'ends':
            case 'end':
                $value = '%'.$value;
                break;
        }
        //$value = strtolower($value);
        $criteria = array('compare'=>self::COMPARE_HAS,'value'=>$value);
        $this->AddCriteriaField($criteria,$field);
        if($andOr) $criteria['andor'] = strtolower($andOr);
        $this->query['criteria'][] = $criteria;
    }

    public function AddNotLikeCriteria($field,$value,$pos=self::LIKE_ANY,$andOr=null) {
        $this->PrecheckCriteria();
        switch($pos) {
            case self::LIKE_STARTS:
                $value.='%';
                break;
            case self::LIKE_ANY:
                $value = '%'.$value.'%';
                break;
            case self::LIKE_ENDS:
                $value = '%'.$value;
                break;
        }
        //$value = strtolower($value);
        $criteria = array('compare'=>self::COMPARE_NOT_HAS,'value'=>$value);
        if($andOr) $criteria['andor'] = strtolower($andOr);
        $this->AddCriteriaField($criteria,$field);
        $this->query['criteria'][] = $criteria;
    }

    public function AddDateRangeCriteria($field,$mintime,$maxtime,$andOr=null) {
        $this->PrecheckCriteria();
        $criteria = array('minunixtime'=>$mintime,'maxunixtime'=>$maxtime);
        $this->AddCriteriaField($criteria,$field);
        if($andOr) $criteria['andor'] = strtolower($andOr);
        $this->query['criteria'][] = $criteria;
    }

    	
    public function AddEqualsCriteria($field,$value=null,$andOr=null) {
        if(is_null($value)) return $this->AddIsNullCriteria($field,$andOr);
        $this->PrecheckCriteria();
        $criteria = array('compare'=>self::COMPARE_EQUALS,'value'=>$value);
        $this->AddCriteriaField($criteria,$field);
        if($andOr) $criteria['andor'] = strtolower($andOr);
        $this->query['criteria'][] = $criteria;
    }

    public function AddNotEqualsCriteria($field,$value=null,$andOr=null) {
        if(is_null($value)) return $this->AddIsNotNullCriteria($field,$andOr);

        $this->PrecheckCriteria();
        $criteria = array('field'=>$field,'compare'=>self::COMPARE_NOT_EQUALS,'value'=>$value);
        $this->AddCriteriaField($criteria,$field);

        if($andOr) $criteria['andor'] = strtolower($andOr);
        $this->query['criteria'][] = $criteria;
    }

    public function AddGroup($field) {
        if(!isset($this->query['groups'])) $this->query['groups'] = array();
        $this->query['groups'][] = $field;
        //$this->query['groups'][] = array('field'=>$field);
    }

    public function AddUpperGroup($field) {
        $this->AddGroup('upper:'.$field);
    }

    public function AddLowerGroup($field) {
        $this->AddGroup('lower:'.$field);
    }

    public function GetQuery() {
       return array_slice($this->query,0);
    }
    public function GetFields() {
        if(!isset($this->query['fields'])) return '*';
        if(count($this->query['fields'])== 0) return '*';
        return array_slice($this->query['fields'],0);
    }
    public function GetSort() {
        if(!isset($this->query['sort'])) return null;
        return array_slice($this->query['sort'],0);
    }
    
}

?>