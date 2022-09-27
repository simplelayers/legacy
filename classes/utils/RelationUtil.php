<?php
namespace utils;


class RelationUtil {
	
	public static function SetRelation($layerId,$url) {
		$info=unserialize($url);

		$db = \System::GetDB(\System::DB_ACCOUNT_SU);
		
		list($table1,$column1,$table2,$column2) = array_values($info);
		$srcTable = $db->GetRow("SELECT * from vectordata_$table1");
		
		$targetTable = $db->GetRow("SELECT * from vectordata_$table2");
		
		$fields = array_keys($srcTable);
		$srcFields = array();
		foreach($fields as $field) {
			if($field=='oid') continue;
			$srcFields[] = 'll.'.$field;	
		}
		
		$fields2 = array_keys($targetTable);
		$targetFields = array();
		
		foreach($fields2 as $field) {
			if($field=='oid') continue;
				
			if(!in_array($field,$fields)) {
				$targetFields[] = 'rl.'.$field;
			}
		}
		$fields = implode(',',array_merge($srcFields,$targetFields));
		$db->Execute('DROP VIEW IF exists public.vectordata_'.$layerId);
		$query= 'CREATE OR REPLACE VIEW public.vectordata_'.$layerId.' AS ';	
		$query.= 'SELECT '.$fields;
		$query.= " FROM vectordata_$table1 as ll JOIN vectordata_$table2 as rl ";
		$query.= " ON ll.$column1 = rl.$column2";
		$db->Execute($query);
		$result = $db->ErrorMsg();
		if($result=='') $result = true;
		
		return $result;
		
	}
	
	public static function ReplaceRelations($fromTable,$toTable,$view =null) {
	    $db = \System::GetDB(\System::DB_ACCOUNT_SU);
	    $view = is_null($view) ? "" : "where table_name='$view'";
	    $relations =$db->Execute("select * from information_schema.views where view_definition like '%{$fromTable}.%' or view_definition like '%{$fromTable} %' $view");

	    foreach($relations as $relation) {
	        $def = $relation['view_definition'];
	        
	        $def = str_replace("{$fromTable}.","{$toTable}.",$def);
	        $def = str_replace("{$fromTable} ","{$toTable} ",$def);
	        $view = $relation['schema'].'.'.$relation['table_name'];
	        $sql = "CREATE OR REPLACE VIEW $view AS $def";
	        
	    }
	    
	}
	
	public static function FromWhereInfo($fromWhere) {
	    $matches = array();
	     
	    
	    $tables = array();
	    $type = "";
	    $join = '';
	    if(strpos($fromWhere,' JOIN ')===false) {
	        
	        preg_match('/ FROM .*/',$fromWhere,$matches);
	        $join = array_pop($matches);
	        $matches=array();
	        
	        preg_match("/FROM ([^W]*)/",$fromWhere,$matches);
	        $match = array_pop($matches);
	        $tables = explode(', ',trim($match));
	        $type = "inner";
	    } elseif(strpos($fromWhere,' JOIN ')) {
	        
	        preg_match("/JOIN ([^WO]*) ON ([^.]*)/",$fromWhere,$tables);
	        $match = array_shift($tables);
	        
	        preg_match("/ FROM {$tables[1]}[^W]*/",$fromWhere,$matches);
	        $join = array_shift($matches);
	        
	       
	        if(strpos($fromWhere, 'LEFT ') !== false) {
	            $type='left'; 
	        } else {
	            $type='right';
	        }
	        
	        //$match = str_replace('JOIN ');
	         
	    }	  
	    
	    
	    return array('tables'=>$tables,'relationType'=>$type,'join'=>$join);
	    
	}
	public static function ResetRelations(\Layer $layer) {
	    
	    $db = \System::GetDB(\System::DB_ACCOUNT_SU); 
	    
	    $viewData = $db->GetRow('select * from information_schema.views where table_name=?',array($layer->url));
	    $definition = $viewData['view_definition'];

	    
	    
	    $info = self::FromWhereInfo($definition);    
	     list($tables,$relationType,$join) = array_values($info);
	    list($table1,$table2) = $tables;
	    $layer1 = \Layer::GetLayerByVectorURL($table1);
	    $layer2 = \Layer::GetLayerByVectorURL($table2);
        
	    $columns = array();
	    // the create depends on the type of JOIN being used
	    //$db->Execute("DROP VIEW if exists {$layer->url}");
	    foreach ($layer1->getAttributes() as $name => $type)
	        $columns[$name] = sprintf("%s.\"%s\"", $table1, $name);
	    foreach ($layer2->getAttributes() as $name => $type)
	        $columns[$name] = sprintf("%s.\"%s\"", $table2, $name);
	    $columns['gid'] = sprintf("%s.%s", $table1, 'gid');
	    //$columns['oid'] = sprintf("%s.%s", $table1, 'oid');
	    if(isset($columns['oid'])) unset($columns['oid']);
	    $columns['the_geom'] = sprintf("%s.%s", $table1, 'the_geom');
	    $columns = array_values($columns);
	    $columns = implode(',', $columns);
	     
	  
	  	switch ($relationType) {
	  	    case 'inner':
	  	        $sql = "CREATE OR REPLACE VIEW {$layer->url} AS SELECT $columns $join";
	  	        break;
	  	    case 'left':
	  	        $sql = "CREATE OR REPLACE VIEW {$layer->url} AS SELECT $columns $join";
	  	        break;
	  	    case 'right':
	  	        $sql = "CREATE OR REPLACE VIEW {$layer->url} AS SELECT $columns $join";
	  	        break;
	  	}
	  	 // old code for reference
    	/*switch ($relationType) {
    	    case 'inner':
    	        $sql = "CREATE OR REPLACE VIEW {$layer->url} AS SELECT $columns FROM $table1, $table2 WHERE $table1.{$tableCol1} = $table2.{$tableCol2}";
    	        break;
    	    case 'left':
    	        $sql = "CREATE OR REPLACE VIEW {$layer->url} AS SELECT $columns FROM $table1 LEFT JOIN $table2 ON $table1.{$tableCol1} = $table2.{$tableCol2}";
    	        break;
    	    case 'right':
    	        $sql = "CREATE OR REPLACE VIEW {$layer->url} AS SELECT $columns FROM $table1 RIGHT JOIN $table2 ON $table1.{$tableCol1} = $table2.{$tableCol2}";
    	        break;
    	}*/
    	
    	
    	$drop = "drop view if exists {$layer->url}";
    	
    	$db->Execute($drop);
    	$db->Execute($sql);
    	
    	
    	
	}
	
	
}



?>