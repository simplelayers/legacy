<?php

class LayerCollection
{
	public static function SetSubs( $world, $parentId, $subs ) {
		self::DeleteSubs( $world, $parentId);
		$subLayers = is_string($subs) ? explode(",",$subs) : $subs;
		for( $z = 0; $z < count($subLayers); $z++) {
			$res= self::AddSub($world,$parentId,$subLayers[$z],$z);
		}	
	}

 	public static function AddSub($world, $parentId, $layerId , $z=null ) {
 		$test = $world->db->Execute("select * from layer_collections where parent_id=? and layer_id=?",array((int)$parentId , (int)$layerId));
 		if($test->RecordCount() > 0) return $test;
		$zCol = ($z === NULL) ? "" : ",z";
		$zQmark = ($z === NULL ) ? "" : ",?";
		$vals = array((int)$parentId,(int)$layerId);
		if($z !== NULL) array_push($vals,(int)$z);
		$world->db->Execute("insert into layer_collections (parent_id,layer_id $zCol) values(?,?$zQmark)",$vals);
 		if($world->db->ErrorNo()) return $world->db->ErrorMsg();
 		return true;
 	}
 	
 	public static function UpdateSub($world,$parentId,$subId,$z) {
 		//$world->db->debug=true;
 		$world->db->Execute("update layer_collections SET z=? where id=? and parent_id=?",array((int)$z,(int)$subId,(int)$parentId));
 	}
 	
 	public static function GetSubs($world,$parentId,$asArray=false,$projectId=null) {
 		$subs = $world->db->Execute( "select * from layer_collections where parent_id=? order by z",array($parentId));
			
		if( !$subs) return $world->db->ErrorMsg();
		if( $asArray ) return $subs->GetRows();
		$sublayers = array();
		
		foreach( $subs as $sub ) {
			array_push($sublayers,$world->getLayerById($sub['layer_id']));
		}
	
		return $sublayers;		
	}
	
	public static function GetSubCount($parentId) {
	    $count = System::GetDB()->GetOne('select count(*) from layer_collections where parent_id=?',array($parentId));
	    return $count;
	}
	
	
	public static function GetProjLayerSubs( $world,$parentId,$asArray=false,$projectId=null) {
		$subs = $world->db->Execute("select id from project_layers where parent=? and project=? order by abs(z)",array($parentId,$projectId));
		if( !$subs) return $world->db->ErrorMsg();
		if( $asArray ) return array_values($subs->GetRows());
		$sublayers = array();
		
		$project = $world->getProjectById( $projectId);
		
		foreach( $subs as $sub ) {
			array_push($sublayers,$project->getLayerById($sub['id']));
		}
		return $sublayers;	
	}
 	 	
 	public static function DeleteSub($world,$parentId,$layerId) {
 	    $subId = $world->db->GetOne("select id from layer_collections where parent_id=? and layer_id=?",array((int)$parentId,(int)$layerId));
 	    $world->db->Execute("delete from layer_collections where parent_id=? and layer_id=?",array((int)$parentId,(int)$layerId));
 	    if($subId) {
 	      $world->db->Execute("update layer_projects set layer_coll_entry_id=null where layer_coll_entry_id=?",array($subId));
 	    }
 	}
 	
 	public static function DeleteSubs( $world,$parentId ) {
 		$world->db->Execute("delete from layer_collections where parent_id=?",array((int)$parentId));
		
 	}
 	
 	public static function SetOrder($world,$parentId,$ids ) {
 		if( is_string($ids) ) $ids =  explode(",",$ids);
 		$subs = $world->db->Execute("select * from layer_collections where parent_id=? order by z",array((int)$parentId));
 		
 		//$world->db->BeginTransaction();
 		$toAdd = $ids;
 		foreach( $subs as $sub) {
			$id = $sub['layer_id'];
			$z = array_search($id,$ids);
			if( $z === false ) {
				self::DeleteSub($world,$parentId, $id);
				continue;
			}
			self::UpdateSub($world,$parentId,$sub['id'],$z);
			unset($toAdd[array_search($id,$toAdd)]);
 		}	
 		
 		foreach($toAdd as $sub ) {
 			self::AddSub($world,$parentId,$sub, array_search($sub,$ids));
 		}
 		
 		//$world->db->EndTransaction();
 		return self::GetSubs($world,$parentId);
 	}
}

?>