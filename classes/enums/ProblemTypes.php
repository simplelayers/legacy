<?php

namespace enums;

class ProblemTypes {
	
	
	const CONTEXT_DMI = 'DMI';
	const CONTEXT_VIEWER = 'Viewer';
	
	const PROBLEM_OBJ_LAYER = 'layer';
	const PROBLEM_OBJ_MAP = 'map';
	
	const PROBLEM_OBJ_SYSTEM = 'system';
	
	const PROBLEM_ANALYSIS_BUFFERING = 'Analysis - Buffering';
	const PROBLEM_ANALYSIS_INTERSECTION = 'Analysis - Intersection';
	const PROBLEM_ATTRIBUTES = 'Attribute Editing';
	const PROBLEM_COPYING = 'Copying';
	const PROBLEM_DETAILS = 'Viewing Details';
	const PROBLEM_EMBEDDING = 'Embedding';
	const PROBLEM_EXPORT_CSV = 'Export - CSV';
	const PROBLEM_EXPORT_GPX = 'Export - GPX';
	const PROBLEM_EXPORT_KML = 'Export - KML';
	const PROBLEM_EXPORT_SHP = 'Export - Shp';
	const PROBLEM_LOGGING = 'Logging';
	const PROBLEM_MAP_VIEWER = 'Viewing in Map Viewer';
	const PROBLEM_METADATA = 'Metadata';
	const PROBLEM_RECORDS = 'Color Scheme Editing';
	const PROBLEM_REMOVING = 'Removing';
	const PROBLEM_SHARING = 'Sharing';
	const PROBLEM_WMS_ACCESS = 'WMS Access';

	const PROBLEM_LAYER_LIST = 'Layers List';
	const PROBLEM_MAP_DETAILS = "Map details problem";
	const PROBLEM_SAVING_LAYER_ORDER = "Saving layer order";
	const PROBLEM_SAVING_LAYER_VISIBILITY = "Saving layer visibility";
	const PROBLEM_SAVING_MAP_DETAILS = "Saving map details";
	const PROBLEM_ORG_LOGO = "Organization logo not displaying";
	const PROBLEM_LEGEND = "Legend";
	const PROBLEM_MAP_TITLE = "Map Title area";
	const PROBLEM_MAP_TOOL_QUERY = "Map Tool: Query";
	const PROBLEM_MAP_TOOL_PAN = "Map Tool: PAN";
	const PROBLEM_MAP_TOOL_ZOOM = "Map Tool: ZOON";
	
	const PROBLEM_LAYER_PROPERTIES = "Layer properties";
	const PROBLEM_LAYER_COLOR_RULES = "Layer classifications";
	const PROBLEM_SAVING_LAYER_PROPERTIES = "Saving layer properties";
	const PROBLEM_SAVING_LAYER_COLOR_RULES = "Saving layer classifications";
	const PROBLEM_SAVING_AS_DEFAULT = "Save As Default";
	const PROBLEM_LAYER_LEGEND = "Layer's Legend";
	const PROBLEM_TOOLTIP = "Tooltip";
	const PROBLEM_LAYER_COLOR_RULES_GEN = "Generate Color Rules";
	
	
	
	private static $problemEnums;
	private static $problemObjectEnum;
	
	public static function GetProblemObjectEnum() {
		if(isset(self::$problemObjectEnum)) return self::$problemObjectEnum;
		$enum = new \Enum();
		$enum->AddItem(self::PROBLEM_OBJ_LAYER);
		$enum->AddItem(self::PROBLEM_OBJ_MAP);
		$enum->AddItem(self::PROBLEM_OBJ_SYSTEM);			
	}
	
	public static function GetEnum($context,$object) {
		if(isset($self::$problemEnums)) {
			if(isset($self::$problemEnums[$context])) {
				if(isset(self::$prblemEnums[$context][$object]))
				return $self::$problemEnums[$context][$object];
			}
		}
		$self::$problemEnums = array();
		
		$dmiMapEnum = new \Enum();
		$dmiMapEnum->AddItem(self::PROBLEM_COPYING);
		$dmiMapEnum->AddItem(self::PROBLEM_DETAILS);
		$dmiMapEnum->AddItem(self::PROBLEM_EMBEDDING);
		$dmiMapEnum->AddItem(self::PROBLEM_LOGGING);
		$dmiMapEnum->AddItem(self::PROBLEM_MAP_VIEWER);
		$dmiMapEnum->AddItem(self::PROBLEM_METADATA);
		$dmiMapEnum->AddItem(self::PROBLEM_REMOVING);
		$dmiMapEnum->AddItem(self::PROBLEM_SHARING);
		$self::$problemEnums[self::CONTEXT_DMI][self::PROBLEM_OBJ_MAP] = $dmiMapEnum;
		
		$dmiLayerEnum = new \Enum();
		$dmiLayerEnum->AddItem(self::PROBLEM_ANALYSIS_BUFFERING);
		$dmiLayerEnum->AddItem(self::PROBLEM_ANALYSIS_INTERSECTION);
		$dmiLayerEnum->AddItem(self::PROBLEM_ATTRIBUTES);
		$dmiLayerEnum->AddItem(self::PROBLEM_COPYING);
		$dmiLayerEnum->AddItem(self::PROBLEM_DETAILS);
		$dmiLayerEnum->AddItem(self::PROBLEM_EMBEDDING);
		$dmiLayerEnum->AddItem(self::PROBLEM_EXPORT_CSV);
		$dmiLayerEnum->AddItem(self::PROBLEM_EXPORT_GPX);
		$dmiLayerEnum->AddItem(self::PROBLEM_EXPORT_KML);
		$dmiLayerEnum->AddItem(self::PROBLEM_EXPORT_SHP);
		$dmiLayerEnum->AddItem(self::PROBLEM_LOGGING);
		$dmiLayerEnum->AddItem(self::PROBLEM_MAP_VIEWER);
		$dmiLayerEnum->AddItem(self::PROBLEM_METADATA);
		$dmiLayerEnum->AddItem(self::PROBLEM_RECORDS);
		$dmiLayerEnum->AddItem(self::PROBLEM_REMOVING);
		$dmiLayerEnum->AddItem(self::PROBLEM_SHARING);
		$dmiLayerEnum->AddItem(self::PROBLEM_WMS_ACCESS);
		$self::$problemEnums[self::CONTEXT_DMI][self::PROBLEM_OBJ_LAYER];
		
		$viewerMapEnum = new \Enum();
		$viewerMapEnum->AddItem(self::PROBLEM_LAYER_LIST);
		$viewerMapEnum->AddItem(self::PROBLEM_MAP_DETAILS);
		$viewerMapEnum->AddItem(self::PROBLEM_SAVING_LAYER_ORDER);
		$viewerMapEnum->AddItem(self::PROBLEM_SAVING_LAYER_VISIBILITY);
		$viewerMapEnum->AddItem(self::PROBLEM_SAVING_MAP_DETAILS);
		$viewerMapEnum->AddItem(self::PROBLEM_ORG_LOGO);
		$viewerMapEnum->AddItem(self::PROBLEM_LEGEND);
		$viewerMapEnum->AddItem(self::PROBLEM_MAP_TITLE);
		$viewerMapEnum->AddItem(self::PROBLEM_MAP_TOOL_QUERY);
		$viewerMapEnum->AddItem(self::PROBLEM_MAP_TOOL_PAN);
		$viewerMapEnum->AddItem(self::PROBLEM_MAP_TOOL_ZOOM);
		$self::$problemEnums[self::CONTEXT_VIEWER][self::PROBLEM_OBJ_MAP] = $viewerMapEnum;
		
		$viewerLayerEnum= new \Enum();
		$viewerLayerEnum->AddItem(self::PROBLEM_LAYER_LIST);
		$viewerLayerEnum->AddItem(self::PROBLEM_LAYER_PROPERTIES);
		$viewerLayerEnum->AddItem(self::PROBLEM_LAYER_COLOR_RULES);
		$viewerLayerEnum->AddItem(self::PROBLEM_LAYER_COLOR_RULES_GEN);
		$viewerLayerEnum->AddItem(self::PROBLEM_SAVING_LAYER_PROPERTIES);
		$viewerLayerEnum->AddItem(self::PROBLEM_SAVING_LAYER_COLOR_RULES);
		$viewerLayerEnum->AddItem(self::PROBLEM_SAVING_AS_DEFAULT);
		$viewerLayerEnum->AddItem(self::PROBLEM_TOOLTIP);
		self::$problemEnums[self::CONTEXT_VIEWER][self::PROBLEM_OBJ_LAYER];
		
	}
}

?>