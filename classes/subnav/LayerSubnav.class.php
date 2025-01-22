<?php

namespace subnav;
use \LayerTypes;
use \AccessLevels;
use Exception;
use \GeomTypes;
use model\Permissions;


class LayerSubnav extends Subnav {
	function makeDefault($layer,$user){
		$session = \SimpleSession::Get();
		$permissions = $session['permissions'];
		
		if($layer->getPermissionById($user->id) >= AccessLevels::EDIT){
			
			if($permissions[':Layers:Details:'] & Permissions::EDIT) $this->add("Edit", "Details", "layer.edit1&id=<!--{\$id}-->");
			if($permissions[':Layers:Metadata:'] & Permissions::EDIT) $this->add("Edit", "Metadata", "layer.metadata1&id=<!--{\$id}-->");
			if($permissions[':Layers:Discussions:'] & Permissions::EDIT) $this->add("Edit", "Discuss", "layer.discussion&id=<!--{\$id}-->");
		}else{
			if($permissions[':Layers:Details:'] & Permissions::VIEW) $this->add("View", "Details", "layer.info&id=<!--{\$id}-->");
			if($permissions[':Layers:Metadata:'] & Permissions::VIEW) $this->add("View", "Metadata", "layer.metadata1&id=<!--{\$id}-->");
			if($permissions[':Layers:Discussions:'] & Permissions::VIEW) $this->add("View", "Discuss", "layer.discussion&id=<!--{\$id}-->");
			
		}
		$this->switchForLayerType($layer, $user);
		
		$this->switchForOwner($layer, $user);
	}
	function switchForOwner($layer, $user){
	    $session = \SimpleSession::Get();
	    $permissions = $session['permissions'];
	    
		$this->assign("id", $layer->id);
		$this->assign("ownerid", $layer->owner->id);
		$this->assign("ownerData", 'owned by <a href="./?do=contact.info&id='.$layer->owner->id.'">'.$layer->owner->username.'</a>');
		$this->assign("lastUpdated", $layer->last_modified);
		$this->assign("objectData", '<a href="./?do=layer.list" >Layers</a> - <a href="./?do=layer.'.(($layer->owner->id == $user->id) ? 'edit1' : 'info').'&id='.$layer->id.'">'.htmlspecialchars ($layer->name).'</a> - '.$layer->geomtypestring);
		if ($layer->owner->id == $user->id) {
			if($layer->type == LayerTypes::VECTOR){
				$this->add("Manage", "Backup", 'javascript: if(confirm(\'Are you sure you want to backup this layer and overwrite the back up stored '.$layer->backuptime .'? This cannot be undone.\')){window.location = \'.?do=layer.backup&id='.$layer->id.'&action=backup\';}');
				if($layer->backup) $this->add("Manage", "Rollback", 'javascript: if(confirm(\'Are you sure you want to restore this layer to its previous state stored '.$layer->backuptime .'? This cannot be undone.\')){window.location = \'.?do=layer.backup&id='.$layer->id.'&action=rollback\';}');
			}
			if($permissions[':Layers:Sharing:'] & Permissions::VIEW) $this->add("Manage", "Sharing", "layer.permissions&id=<!--{\$id}-->");
			if($permissions[':Layers:Usage:'] & Permissions::VIEW) $this->add("Manage", "Usage", "layer.statistics&id=<!--{\$id}-->");
			if($permissions[':Layers:General:'] & Permissions::DELETE)$this->add("Manage", "Delete", 'javascript: if(confirm(\'Are you sure you want to delete this layer?\nThere is no way to un-delete or recover a layer once it has been deleted.\n\nClick OK to delete this layer.\nClick Cancel to NOT delete this layer.\')){window.location = \'.?do=layer.delete&id='.$layer->id.'\';}');
		}
			if ($user->isLayerBookmarkedById($layer->id)) {
				$this->assign("edit", '<a href="./?do=layer.removebookmark&id='.$layer->id.'"><img src="media/icons/book_delete.png"/></a>');
			}else {
				$this->assign("edit", '<a href="./?do=layer.addbookmark&id='.$layer->id.'"><img src="media/icons/book_add.png"/></a>');
			}
	}
	function switchForLayerType($layer, $user){
	    $session = \SimpleSession::Get();
	    $permissions = $session['permissions'];
	    
		switch($layer->type){
			case  LayerTypes::VECTOR:
				
				if($layer->getPermissionById($user->id) >= AccessLevels::EDIT){				
					if($permissions[':Layers:Attributes:'] & Permissions::EDIT) $this->add("Edit", "Attributes", "vector.attributes&id=<!--{\$id}-->");
					if($permissions[':Layers:Records:'] & Permissions::EDIT) $this->add("Edit", "Records", "vector.records&id=<!--{\$id}-->");
					if($permissions[':Layers:Classifications:'] & Permissions::EDIT)$this->add("Edit", "Color Scheme", "default.colorscheme&id=<!--{\$id}-->");
				}elseif($layer->getPermissionById($user->id) >= AccessLevels::READ){
					if($permissions[':Layers:Records:'] & Permissions::VIEW)
					$this->add("View", "Records", "vector.records&id=<!--{\$id}-->");
				}
				if($layer->getPermissionById($user->id) >= AccessLevels::COPY){
					if($permissions[':Layers:Formats:KML:'] & Permissions::SAVE) $this->add("Export As", "KML", "download.kml&id=<!--{\$id}-->");
					if($permissions[':Layers:Formats:GPS:'] & Permissions::SAVE) {if(($layer->geomtype == GeomTypes::POINT) || ($layer->geomtype == GeomTypes::LINE))$this->add("Export As", "GPX", "download.gpx&id=<!--{\$id}-->");}
					if($permissions[':Layers:Formats:SHP:'] & Permissions::SAVE) $this->add("Export As", "SHP", "download.shp&id=<!--{\$id}-->");
					if($permissions[':Layers:Formats:CSV:'] & Permissions::SAVE) $this->add("Export As", "CSV", "download.csv&id=<!--{\$id}-->");
					
					if($permissions[':Layers:Analysis:Buffer:'] & Permissions::CREATE) $this->add("Analysis", "Buffer", "layer.analysisbuffer1&id=<!--{\$id}-->");
					if($permissions[':Layers:Analysis:Intersection:'] & Permissions::CREATE) $this->add("Analysis", "Intersection", "layer.analysisintersection1&id=<!--{\$id}-->");
				}
			
			break;
			case LayerTypes::RASTER:
				if($layer->getPermissionById($user->id) >= AccessLevels::COPY){
					if($permissions[':Layers:Formats:RASTER:'] & Permissions::SAVE) {
						$this->add("Export As", "TIFF", "download.raster&id=<!--{\$id}-->");
						$this->add("Export As", "JPEG", "download.image&id=<!--{\$id}-->");
					}
				}
			break;
			case LayerTypes::WMS:
			break;
			case LayerTypes::ODBC:
			break;
			case LayerTypes::RELATIONAL:
				if($layer->getPermissionById($user->id) >= AccessLevels::EDIT){
					if($permissions[':Layers:Attributes:'] & Permissions::EDIT)	$this->add("Edit", "Attributes", "vector.attributes&id=<!--{\$id}-->");
					if($permissions[':Layers:Records:'] & Permissions::EDIT)	$this->add("Edit", "Records", "vector.records&id=<!--{\$id}-->");
					if($permissions[':Layers:Classifications:'] & Permissions::EDIT) $this->add("Edit", "Color Scheme", "default.colorscheme&id=<!--{\$id}-->");
					if($permissions[':Layers:Relations:'] & Permissions::EDIT) $this->add("Edit", "Relations", "layer.relations1&id=<!--{\$id}-->");					
				}elseif($layer->getPermissionById($user->id) >= AccessLevels::READ){
					if($permissions[':Layers:Records:'] & Permissions::VIEW) $this->add("View", "Records", "vector.records&id=<!--{\$id}-->");
				}
				
				if($layer->getPermissionById($user->id) >= AccessLevels::COPY){
					if($permissions[':Layers:Formats:KML:'] & Permissions::SAVE) $this->add("Export As", "KML", "download.kml&id=<!--{\$id}-->");
					if($permissions[':Layers:Formats:GPS:'] & Permissions::SAVE) {if(($layer->geomtype == GeomTypes::POINT) || ($layer->geomtype == GeomTypes::LINE))$this->add("Export As", "GPX", "download.gpx&id=<!--{\$id}-->");}
					if($permissions[':Layers:Formats:SHP:'] & Permissions::SAVE) $this->add("Export As", "SHP", "download.shp&id=<!--{\$id}-->");
					if($permissions[':Layers:Formats:CSV:'] & Permissions::SAVE) $this->add("Export As", "CSV", "download.csv&id=<!--{\$id}-->");
				
					if($permissions[':Layers:Analysis:Buffer:'] & Permissions::CREATE) $this->add("Analysis", "Buffer", "layer.analysisbuffer1&id=<!--{\$id}-->");
					if($permissions[':Layers:Analysis:Intersection:'] & Permissions::CREATE) $this->add("Analysis", "Intersection", "layer.analysisintersection1&id=<!--{\$id}-->");
					
				}
			break;
			case LayerTypes::COLLECTION:
			break;
			default:
			break;
		}
	}
}
?>