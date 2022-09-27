<?php

namespace layer_utils;
use \System;
class LayerTypeSearches {
	
	public static function GetLayersByType($type, $owner=null) {
		if(!\LayerTypes::IsValidType($type) ) return false;
		$db = \System::GetDB(System::DB_ACCOUNT_SU);
		
		$query = "select * from layers where type=$type";
		if(!is_null($owner)) $query.= ' and owner='.$owner;
		
		$cursor = $db->Execute($query);
		
		return $cursor;
		
	}
	
	public static function GetLayersByGeomType($type,$owner=null) {
		if(!\GeomTypes::IsValidType($type) ) return false;
		$db = \System::GetDB(System::DB_ACCOUNT_SU);
		
		$query = "select * from layers where geom_type=$type";
		if(!is_null($owner)) $query.= ' and owner='.$owner;
		
		$cursor = $db->Execute($query);
		
		return $cursor;				
	}
	
	
	
}

?>