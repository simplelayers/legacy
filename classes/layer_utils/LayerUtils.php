<?php

namespace layer_utils;

class LayerUtils {
	
	public static function ToTypeString($layer) {
		$layer = self::ToLayerRecord($layer);
		
		if($layer['type'] == \LayerTypes::VECTOR || \LayerTypes::RELATIONAL ) {
			$geomTypes = \GeomTypes::GetEnum();
			return $geomTypes[$layer['geom_type']];			
		}
		$layerTypes = \LayerTypes::GetEnum();
		return $layerTypes[(int) $layer['type']];		
	}
	
	public static function ToGeomTypeString($layer) {
		$layer = self::ToLayerRecord($layer);
		if(!in_array((int)$layer['type'] , array( \LayerTypes::VECTOR , \LayerTypes::RELATIONAL))) {
			
			return \GeomTypes::UNKNOWN;
		}

		$geomTypes = \GeomTypes::GetEnum();
		if(!$geomTypes->IsItem((int)$layer['geom_type'])) {
			return \GeomTypes::UNKNOWN;
		}
		return $geomTypes[(int)$layer['geom_type']];
	}
	
	
	
	public static function GetLayerFromRecord($layerRecord) {
		
	}
	
	private static function ToLayerRecord($layer) {
		if($layer instanceof \ProjectLayer) {
			$layer = $layer->layer;
		}
		
		if($layer instanceof \Layer) {
			$layer = $layer->GetLayerRecord();
		}			
		
		return $layer;
		
	}
	

	
	
	
}

?>