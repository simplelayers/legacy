<?php

namespace utils;

class ExtentUtil {
	public static function ParseExts($ext,$separator=',',&$minx,&$miny,&$maxx,&$maxy){ 
		list($x1,$y1,$x2,$y2) = explode($separator,$ext);
		$minx = min($x1,$x2);
		$miny = min($y1,$y2);
		$maxx = max($x1,$x2);
		$maxy = max($y1,$y2);
		return array($minx,$miny,$maxx,$maxy);
	}
	
	public static function SetMSExtents($mapobj,$exts,$separator=',') {
		$minx = $miny = $maxx= $maxy = 0;
		$extents = self::ParseExts($project->bbox,',',$minx, $miny, $maxx, $maxy);
		$mapObj->extent->setextent ( $minx, $miny, $maxx, $maxy);
		return $extents;
	}
}

?>