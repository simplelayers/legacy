<?php
namespace viewer;

class Features {
	
	const NONE =		 0;
	const LOGO =		 1;
	const TOOLPALETTE =	 2;
	const NAVMAP =		 4;
	const LAYERLIST =	 8;
	const CURSORINFO =	16;
	const PROJECTINFO =	32;
	const LEGEND =		64;
	const RESULTS =		128;
	const SIMPLENAV =	256;
	const ALL =			511;
	
	const DEFAULT_FEATURES = 255; //ALL-SIMPLENAV
	const DEFAULT_EMBED = self::NONE;

	private static $enum = NULL;
		
	public static function GetEnum($replace=false) {
		if( (self::$enum !==NULL) and !$replace ) return self::$enum;
		self::$enum = new \FlagEnum('Logo','Tool Palette','Nav Map','Layers List','Cursor Info','Map Info','Legend','Results','Simple floating tool palette');
		self::$enum->AddItem('none',0);
		return self::$enum;
	}
	

}