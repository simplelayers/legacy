<?php
use utils\ParamUtil;
use utils\Pixospatial;
use model\SL_Query;

/**
 * Build search criteria procedurally based on $_REQUEST variables and other runtime data
 * then retrieve SQL query fragments.
 */
class SearchCriteria
{

    const INTERSECT_MODE_NONE = 0;
    const INTERSECT_MODE_INTERSECT = 1;
    const INTERSECT_MODE_NOT_INTERSECT = -1;
    const INTERSECT_MODE_WITHIN = 2;
    const INTERSECT_MODE_NOT_WITHIN = -2;
    
    
    public $paging;

    public $criteria = array();

    public $sort;

    public $table;

    public $fields;

    public $stats;

    public $limit;

    public $groups;
    
    public $includeGeometry =false;
    public $numericFields = array();
    public function __construct($table, $args = null, $criteriaAsJSON = true)
    {   
        $this->table = $table;
            
        
        
        if(is_a($args,'model\SL_Query')) {
            $query  = $args->GetQuery();
            $this->criteria = $query['criteria'];
            $this->fields = $args->GetFields();
            $this->sort = $args->GetSort();
            $this->paging = new Paging();
            
        } elseif (is_null($args)) {
            $this->paging = new Paging();
            $sort = ParamUtil::Get($args, RequestUtil::Get('sort', null));
            $sort = json_decode($sort, true);
            $this->sort = $sort;
            $this->limit = RequestUtil::Get('limit', null);
            $this->idField = ParamUtil::Get($args,'idField',null);
            $this->groups = RequestUtil::Get('groups', null);
            if (! is_null($this->groups))
                explode(',', $this->groups);
            $this->fields = RequestUtil::Get('fields', null);
            if (! is_null($this->fields))
                $this->fields = json_decode($this->fields, true);
            $this->criteria = RequestUtil::Get('criteria', null);
        } else {
            $this->paging = new Paging("first", "limit", "count", $args);
            $sort = ParamUtil::Get($args, 'sort', null);
            $sort = json_decode($sort, true);
            $this->sort = $sort;
            $this->idField = ParamUtil::Get($args,'idField',null);
            $this->limit = ParamUtil::Get($args, 'limit', null);
            $this->groups = ParamUtil::Get($args, 'groups', null);
            if (! is_null($this->groups))
                explode(',', $this->groups);
            $this->fields = ParamUtil::Get($args, 'fields', null);
            $this->criteria = ($criteriaAsJSON) ? ParamUtil::GetJSON($args, 'filters', null) : ParamUtil::Get($args, 'filters', ';;');
            $this->includeGeometry = array_shift(ParamUtil::GetBoolean($args,'geom'));
        }
        
        if(in_array($this->criteria,array('[]','',null))) {
            $this->criteria= array();
            $this->criteria[]= array(
                'field' => 'gid',
                'compare' => Comparisons::COMPARE_NOT_ISNULL,
                'value' => ''
            );
        }
        
        $this->stats = RequestUtil::Get('stats', null);
        if (! is_null($this->stats))
            $this->stats = json_decode($this->stats, true);
        if (is_null($this->stats))
            $this->stats = array();
        if ($this->fields == '')
            $this->fields = "*";
            // sanitize the WHERE clause
        
        
        if (is_array($this->criteria)) {
            // nothing to do
        } elseif (substr($this->criteria, 0, 1) == '[') {
            $this->criteria = json_decode($this->criteria, true);
        } elseif (count($this->criteria) > 0) {
            $criData = explode(";", RequestUtil::Get('criteria', ';;'));
            
            list ($c1, $c2, $c3) = $criData;
            $isNumeric = in_array($c1,$this->numericFields);
            if ($c1 == '')
                return;
            switch (strtolower($c2)) {
                case 'has':
                case 'contains':
                case 'like':
                case '~':                    
                    $c1 = "coalesce({$c1}::text)";
                    $c2 = 'LIKE';
                    $c3 = "%$c3%";
                    break;
                case '!has':
                case '!contains':
                case '!~':
                    $c1 = "coalesce({$c1}::text)";
                    $c2 = 'NOT LIKE';
                    $c3 = "%$c3%";
                    break;
                case '==':
                    $c1 = "coalesce($c1::text)";
                    $c2 = "=";            
                    $c3 = "$c3";
            }
            
            
            $this->criteria[] = array(
                'field' => $c1,
                'compare' => $c2,
                'value' => $c3
            );
            
        }
      
    }

    public function SetNumericFields($numericFields) {
        $this->numericFields = $numericFields;
    }
    
    public function GetQuery($distinct = false, $bbox=null, $gids = null, $memoryLayer = null, $intersectionMode = 0, $buffer = null)
    {
        $as_query = RequestUtil::Get('as_area', false);
        
        
        if ($as_query)
            return $this->GetAreaQuery();
        $fields = ($this->fields == "*") ? $this->fields : $this->GetFields($distinct);
        
        $geometry = "";
        
        $bboxQuery = "";
        if(!is_null($bbox)) {
            if(!is_array($bbox)) $bbox = explode(',',$bbox);
            list ($llx, $lly, $urx, $ury) = $bbox;
            $llx = (float) $llx;
            $lly = (float) $lly;
            $urx = (float) $urx;
            $ury = (float) $ury;
            $bboxQuery = "st_intersects(the_geom,st_GeometryFromText('POLYGON(($llx $lly, $llx $ury, $urx $ury, $urx $lly, $llx $lly))',4326))";
            
        }
        
       $gidsFilter = "";
        if (! is_null($gids)) {
            if (is_array($gids))
                $gids = implode(',', $gids);
            $geomField = is_null($buffer) ? 'the_geom as the_buff_geom' : "ST_TRANSFORM(ST_Buffer(ST_TRANSFORM(the_geom,3857),$buffer),4326) as the_buff_geom";
            $gidsFilter = "(select $geomField from vectordata_{$memoryLayer} where gid in ({$gids}) AND the_geom IS NOT NULL) as memoryQuery";
        }
      
        $intersectQuery = "";
       
        switch ($intersectionMode) {
            case self::INTERSECT_MODE_NONE:
                break;
            case self::INTERSECT_MODE_INTERSECT:
                $intersectQuery = "(select distinct(src.gid) from {$this->table}  as src ,$gidsFilter where SL_Intersects(src.the_geom,memoryQuery.the_buff_geom))";
                break;
            case self::INTERSECT_MODE_NOT_INTERSECT:
                $intersectQuery = "(select distinct(src.gid) from {$this->table} as src ,$gidsFilter where not SL_Intersects(src.the_geom,memoryQuery.the_buff_geom))";
                break;
            case self::INTERSECT_MODE_WITHIN:
                $intersectQuery = "(select distinct(src.gid) from {$this->table} as src ,$gidsFilter where ST_WITHIN(src.the_geom,memoryQuery.the_buff_geom) OR ST_Equals(src.the_geom,memoryQuery.the_buff_geom))";
                break;
            case self::INTERSECT_MODE_NOT_WITHIN:
                $intersectQuery = "(select distinct(src.gid) from {$this->table} as src ,$gidsFilter where NOT (ST_WITHIN(src.the_geom,memoryQuery.the_buff_geom) OR ST_Equals(src.the_geom,memoryQuery.the_buff_geom)))";
                break;
        }
        
        $where = $this->GetWhere();
        
        $intersectQuery = (is_null($gids))  ? null : "gid in $intersectQuery";
        $sections = array();
        $sections[] = $where;
        if(!is_null($intersectQuery)) {
            $sections[]=  "st_isvalid(the_geom) AND $intersectQuery";
           
        }
        if(!is_null($bboxQuery))  $sections[] = $bboxQuery;
        $fullWhere = "";
        $i = 0;
        foreach ($sections as $section) {
            if ($i == 0) {
                $fullWhere .= $section;
                $i ++;
                continue;
            }
            if ($section != "") {
                if ($fullWhere != "") {
                    $fullWhere .= ' AND ';
                }
                $fullWhere .= $section;
            }
            $i ++;
        }
       
        if ($fullWhere != "") {
            if (strlen($fullWhere) > 5) {
                if (substr($fullWhere, 0, 5) != "WHERE") {
                    
                    $fullWhere = "WHERE " . $fullWhere;
                }
            } else {
                $fullWhere = "WHERE " . $fullWhere;
            }
        }
      
        if($this->includeGeometry === true) $fields.=',st_AsText(the_geom) as the_geom';
        
        $query = "SELECT $fields FROM {$this->table} {$fullWhere} {$this->GetGrouping()} {$this->GetOrder()} " . $this->paging->toQueryString();
        
                 return $query;
    }
    
    public function GetAliasCountQuery($alias,$distinct = false, $bbox=null, $gids = null, $memoryLayer = null, $intersectionMode = 0, $buffer = null) {
        return  "SELECT count(*) as $alias from (".$this->GetQuery($distinct, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer).") as $alias";
    }
    public function GetCountQuery($distinct = false, $bbox=null, $gids = null, $memoryLayer = null, $intersectionMode = 0, $buffer = null) {
        
        return "SELECT count(*) from (".$this->GetQuery($distinct, $bbox, $gids, $memoryLayer, $intersectionMode, $buffer).") as q1";
    }

    public function GetLimit()
    {
        return (! is_null($this->limit)) ? " LIMIT " . $this->limit : "";
    }

    public function GetAreaQuery($geomField = 'the_geom')
    {
        $q = "SELECT MIN(x1) as x1,MIN(y1) as y1, MAX(x2) as x2, Max(y2) as y2 from( SELECT st_xMin($geomField) as x1, st_yMin($geomField) as y1, st_xMax($geomField) as x2,st_yMax($geomField) as y2 FROM {$this->table} {$this->GetWhere()}) as q1";
        
        // error_log($q);
        return $q;
    }
    
    

    private function GetOrder()
    {
        $sort = "";
        $started = false;
        if (is_null($this->sort)) {
            
            return 'ORDER BY gid';
        }
            
        
        foreach ($this->sort as &$sortItem) {
            $sort .= ($started) ? "," : "";
            
            $item = explode(':', $sortItem['field']);
            $sortField = array_pop($item);
            
            if (count($item)) {
                $operation = $item[0];
                switch ($operation) {
                    case 'upper':
                        $sortItem['field'] = "upper(" . $sortField . ")";
                        break;
                    case 'lower':
                        $sortItem['field'] = "lower(" . $sortField . ")";
                        break;
                    case 'substr':
                        $from = array_shift($sortItem);
                        $to = count($sortItem) ? array_shift($item) : null;
                        if (! is_null($to)) {
                            $sortItem['field'] = "substr(" . $sortField . "," . $from . ")";
                        } else {
                            $sortItem['field'] = "substr(" . $sortField . "," . $from . "," . $to . ")";
                        }
                        break;
                }
            }
            
            $sort .= ' ' . $sortItem['field'];
            error_log($sort);
            if (isset($sortItem['type']))
                $sort .= "::" . $sortItem['type'];
            if (isset($sortItem['direction']))
                $sort .= " " . trim($sortItem['direction']);
            $started = true;
        }
        if (trim($sort) != "")
            $sort = "ORDER BY " . $sort;
        return $sort;
    }

    protected function GetGrouping()
    {
        $groupBy = "";
        if (is_null($this->groups))
            return $groupBy;
        $groupBy .= " GROUP BY ";
        
        if (is_string($this->groups)) {
            $this->groups = explode(',', $this->groups);
        }
        
        $isFirst = true;
        foreach ($this->groups as $group) {
            $groupInfo = explode(':', $group);
            $groupItem = array_pop($groupInfo);
            
            if (count($groupInfo)) {
                $operation = $groupInfo[0];
                switch ($operation) {
                    case 'upper':
                        
                        $groupItem = "upper($groupItem)";
                        break;
                    case 'lower':
                        $groupItem = "lower($groupItem)";
                        break;
                    case 'substr':
                        $from = array_shift($groupItem);
                        $to = count($groupItem) ? array_shift($groupItem) : null;
                        if (! is_null($to)) {
                            $groupItem = "substr($groupItem,$from)";
                        } else {
                            $groupItem = "substr($groupItem,$from,$to)";
                        }
                        break;
                }
            }
            $groupBy .= ($isFirst) ? '' : ',';
            $groupBy .= $groupItem;
            if ($isFirst)
                $isFirst = false;
        }
        
        return $groupBy;
    }

    private function GetFields($distinct = false, $honorVis = false)
    {
        
        $fields = "";
        $db = System::GetDB(System::DB_ACCOUNT_SU);
        $started = 0;
        
        foreach ($this->fields as $fieldInfo) {
            if ($started)
                $fields .= ',';
            
            if (isset($fieldInfo['distinct']))
                $fields .= $fieldInfo['distinct'] ? 'DISTINCT ' : '';
            if ($fieldInfo['field'] == 'gid')
                unset($fieldInfo['type']);
            if (isset($fieldInfo['type'])) {
                
                $fieldInfoParams = stripos($fieldInfo['type'], ':') ? explode(':', $fieldInfo['type']) : array();
                if (count($fieldInfoParams)) {
                    $fieldInfo['type'] = array_shift($fieldInfoParams);
                }
                
                switch ($fieldInfo['type']) {
                    case 'date':
                        $field = $fieldInfo['field'];
                        $field .= '::date';
                        break;
                    case 'epoch':
                        $field .= " DATE_PART('epoch'," . $fieldInfo['field'] . "::timestamp)";
                        break;
                    case 'substr':
                        list ($from, $to) = $fieldInfoParams;
                        $field .= " substr(" . $fieldInfo['field'] . ",$from,$to)";
                        break;
                    case 'upper':
                        $field .= " upper(" . $fieldInfo['field'] . ")";
                        break;
                    case 'lower':
                        $field .= " lower(" . $fieldInfo['field'] . ")";
                    case 'text input':
                    case 'text area':
                        $field .= $db->IfNull("''||" . $fieldInfo['field'], "''");
                        if (! isset($fieldInfo['as']))
                            $fieldInfo['as'] = $fieldInfo['field'];
                        break;
                    case 'float':
                    case 'numeric':
                        $field .= $db->IfNull($fieldInfo['field'], "'NaN'");
                        if (! isset($fieldInfo['as']))
                            $fieldInfo['as'] = $fieldInfo['field'];
                        break;
                    default:
                        $field .= $fieldInfo['field'];
                        break;
                }
                // TODO support funky types
            } else {
                $field = $fieldInfo['field'];
                if (! isset($fieldInfo['as']))
                    $fieldInfo['as'] = $fieldInfo['field'];
            }
            $fields.= "\"$field\"";
            if (isset($fieldInfo['as']))
                $fields .= ' as ' . "\"{$fieldInfo['as']}\"";
            $started = 1;
        }
        
        foreach ($this->stats as $statField) {
            $sep = ($started) ? ',' : '';
            switch (strtolower($statField['stat'])) {
                case 'max':
                    $fields .= "$sep MAX(" . $statField['field'] . ") as " . $statField['as'];
                    break;
                case 'min':
                    
                    $fields .= "$sep MIN(" . $statField['field'] . ") as " . $statField['as'];
                    break;
            }
            $started = 1;
        }
        
        return $fields;
    }

    private function GetWhere()
    {
        $where = '';
        $i = 0;
        $groupsStart = 0;
        $groupsEnd = 0;
        $groupBegun = false;
        
        if (is_null($this->criteria))
            $this->criteria = array();
        
        foreach ($this->criteria as $criterion) {
            
            $andor = isset($criterion['andor']) ? $criterion['andor'] : '';
            
            $startGroup = ParamUtil::Get($criterion, 'group', null);
            $groupBegun = false;
            if ($startGroup) {
                if (! isset($criterion['field'])) {
                    $where .= " $andor (";
                } else {
                    $where .= ($i > 0) ? " $andor (" : " (";
                }
                $groupsStart += 1;
                $groupBegun = true;
                if (! isset($criterion['field']))
                    continue;
            }
            $endGroup = ParamUtil::Get($criterion, '!group', ParamUtil::Get($criterion, 'group_end', null));
            if ($endGroup) {
                if (! isset($criterion['field']))
                    $where .= ')';
                
                $groupsEnd += 1;
                if (! isset($criterion['field']))
                    continue;
            }
            if ($i > 0 || $groupBegun) {
                if ($groupBegun) {
                    $andor = '';
                    $groupBegun = false;
                } else {
                    $andor = ($andor == '') ? 'and' : $andor;
                }
                $andor = strtolower(trim($andor));
                $isOk = in_array($andor, array(
                    'and',
                    'or',
                    ''
                ));
                if (! $isOk)
                    throw new Exception("Unauthorized Query: andor value ($andor) is not an approved value");
                $where .= ' ' . $andor . ' ';
            }
            
            if (isset($criterion['minunixtime'])) {
                $min = $criterion['minunixtime'];
                $max = $criterion['maxunixtime'];
                $field = $criterion['field'];
                $where .= "(DATE_PART('epoch'," . $field . "::timestamp) >= $min) AND (DATE_PART('epoch'," . $field . "::timestamp) <= $max)";
            } else {
                $modifier = isset($criterion['modifier']) ? $criterion['modifier'] : null;
                
                $negate = isset($criterion['is_not']) ? $criterion['is_not'] : false;
                if ($criterion['compare'] == Comparisons::COMPARE_ISNULL) {
                    foreach ($this->fields as $fieldInfo) {
                        if ($fieldInfo['field'] == $criterion['field'])
                            break;
                    }
                    if ($fieldInfo) {
                        switch (($fieldInfo['type'])) {
                            case 'int':
                            case 'float':
                            case 'numeric':
                                $criterion['compare'] = Comparisons::COMPARE_ISNAN;
                        }
                    }
                }
                
                $where .= Comparisons::ToQueryWhere($criterion['field'], $criterion['compare'], $criterion['value'], $modifier, false, $negate);
                
                if ($endGroup && isset($criterion['field'])) {
                    $where .= ')';
                }
            }
            
            $i ++;
        }
        
        if ($groupsEnd > $groupsStart) {
            throw new Exception('Unbalanced ( groups ) in  query criteria');
        }
        if ($groupsEnd < $groupsStart) {
            $groupsToEnd = $groupsStart - $groupsEnd;
            for ($i = 0; $i < $groupsToEnd; $i ++) {
                $where .= ')';
            }
        }
        
        if (count($this->criteria) == 0)
            $where = '';
        if (trim($where) != "")
            return "WHERE " . $where;
        
        return "";
    }
}

?>