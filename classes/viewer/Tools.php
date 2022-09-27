<?php
namespace viewer;

class Tools {
// define the access levels
// features and tools used by the viewer

	const NONE = 0;
	const ANNOTATE = 1;
	const DIGITIZE = 2;
	const PAN =  4;
	const QUERY =    8;
	const SEARCH =  16;
	const SNAPSHOT = 32;
	const ZOOM =    64;
	const QUERYURL= 128;
	const TOOLTIP = 256;
	const RELATADATA = 512;
	const EVERYTHING = 1024;
	const ALL = 2047;
	const DEFAULT_TOOLS = 1407; // ALL - RELATADATA - QUERYURL
	const DEFAULT_EMBED = 2175;  // QUERYURL + VIEWERTOOL_TOOLTIP; // default settings
	
	private static $enum = NULL;
	
	public static function GetEnum($replace=false) {
		if( (self::$enum !==NULL) and !$replace ) return self::$enum;
		self::$enum = new \FlagEnum('Annotate','Digitize','Pan','Query','Search','Snapshot','Zoom','Query URL','Tool Tip');
		self::$enum->AddItem('None',self::NONE);
	}


}