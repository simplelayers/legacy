<?php

namespace utils;



class Pixospatial{
	private $minLon;
	private $minLat;
	private $maxLon;
	private $maxLat;
	
	private $degWidth;
	private $degHeight;
	
	private $viewWidth;
	private $viewHeight;
	
	private $centerLon;
	private $centerLat;
	
	private $degWidthHeight;
	private $degHeightWidth;

	private $pxWidthHeight;
	private $pxHeightWidth;
	
	private $propWidth;
	private $propHeight;
	
	private $adjWidth;
	private $adjHeight;
	
	private $halfWidth;
	private $halfHeight;
	
	private $adjMinLon;
	private $adjMinLat;
	private $adjMaxLon;
	private $adjMaxLat;
	
	var $scales = array(314982288,157491144,78745572,39372786,19686393,9843196.5,4921598.25,2460799.125,1230399.5625,615199.78125,307599.890625,153799.9453125,76899.97265625,38449.986328125,19224.993164062,9612.4965820312,4806.2482910156,2403.1241455078,1201.5620727539,600.78103637695);
	var $degPerPxAtLevel=array(1,0.5,0.25,0.125,0.0625,0.03125,0.015625,0.007813889,0.003905556,0.001952778,0.000977778,0.000488889,0.000244444,0.000122222,6.11111E-05,3.05556E-05,1.38889E-05,8.33333E-06,2.77778E-06,1.38889E-06);
	//var $latDegPerPxAtLevel=array(1,0.5,0.25,0.125,0.0625,0.03125,0.015625,0.007813889,0.003905556,0.001952778,0.000977778,0.000488889,0.000244444,0.000122222,6.11111E-05,3.05556E-05,1.38889E-05,8.33333E-06,2.77778E-06,1.38889E-06);
	var $degPerLevel = array(360,180,90,45,22.5,11.25,5.625,2.8125,1.40625,0.703125,0.3515625,0.17578125,0.087890625,0.0439453125,0.02197265625,0.010986328125,0.0054931640625,0.00274658203125,0.001373291015625,0.0006866455078125,0.00034332275390625);
	
	
	public static function Get($bbox, $pxWidth, $pxHeight) {
		if (! is_array ( $bbox ))
			$bbox = explode ( ',', $bbox );
		list ( $lon1, $lat1, $lon2, $lat2 ) = $bbox;
		return new Pixospatial ( $lon1, $lat1, $lon2, $lat2, $pxWidth, $pxHeight );
	}
	public function __construct($lon1, $lat1, $lon2, $lat2, $pxWidth, $pxHeight) {
		$this->minLon = doubleval ( min ( $lon1, $lon2 ) );
		$this->maxLon = doubleval ( max ( $lon1, $lon2 ) );
		$this->degWidth = $this->maxLon - $this->minLon;
		
		$this->minLat = doubleval ( min ( $lat1, $lat2 ) );
		$this->maxLat = doubleval ( max ( $lat1, $lat2 ) );
		$this->degHeight = $this->maxLat - $this->minLat;
		$this->viewWidth = $pxWidth;
		$this->viewHeight = $pxHeight;
		
	
	}
	
	public function FitToLevel($targetLevel) {
	    
	    $lonDelta = $this->maxLon - $this->degWidth;
	    $latDelta = $this->maxLat - $this->degheight;
	     
	    $delta = max($lonDelta,$latDelta);
	    $maxDim = ($lonDelta == $delta) ? 'lon': 'lat';
	     
	    $degPerLevels = array_slice($this->degPerLevel,0,20,true);
	    $degAtCurrentLevel = $degPerLevels[$targetLevel];
	    $degPerPxAtCurrentLevel = $degPerLevels[$targetLevel];

	    $halfWidth = $this->viewWidth/2.0;
	    $halfHeight = $this->viewHeight/2.0;
	    
	    $halfLonDeltaNew = $halfWidth * $this->degPerLevel[$targetLevel];
	    $halfLatDeltaNew = $halfHeight * $this->degPerLevel[$targetLevel+1];
	    
	    $this->minLon = $this->centerLon-$halfLonDeltaNew;
	    $this->minLat = $this->centerLat-$halfLatDeltaNew;
	    $this->maxLon = $this->centerLon+$halfLonDeltaNew;
	    $this->maxLat = $this->centerLat+$halfLatDeltaNew;
	    
	    
	    
	    
	}
	
	public function NextLevel() {
	    $lonDelta = $this->maxLon - $this->degWidth;
	    $latDelta = $this->maxLat - $this->degheight;
	     
	    $delta = max($lonDelta,$latDelta);
	    $maxDim = ($lonDelta == $delta) ? 'lon': 'lat';
	     
	    $degPerLevels = ($maxDim=='lon') ? array_slice($this->degPerLevel,0,20,true) : array_slice($this->degPerlevel,1,20,true);
	    $degAtCurrentLevel = $degPerLevels[$targetLevel];
	    foreach($degPerLevels as $level=>$degAtLevel) {
	        if($degAtCurrentLevel <= $degAtLevel) {
	            break;
	        }
	    }
	    if($level < 20) $level++;
	    if($level >=20) $level = 20;
	    $this->FitToLevel($level);
	}
	
	public function FitToView() {
	    
	    
	    
		$this->centerLon = $this->maxLon - ($this->degWidth / 2);
		$this->centerLat = $this->maxLat - ($this->degHeight / 2);
	
		$this->degWidthHeight = $this->degWidth / $this->degHeight;
		$this->degHeightWidth = $this->degHeight / $this->degWidth;
		
		$this->pxWidthHeight = doubleval ( $this->viewWidth / $this->viewHeight );
		$this->pxHeightWidth = doubleval ( $this->viewHeight / $this->viewWidth );
		
		$this->propWidth = ($this->degHeight / $this->viewHeight) * $this->viewWidth;
		$this->propHeight = ($this->degWidth / $this->viewWidth) * $this->viewHeight;
		
		
		$taller = $this->viewHeight > $this->viewWidth;
		$taller2 = $this->degHeight > $this->degWidth;
		
		//$wider2 = $this->degWidth >= $this->degHeight;
		
		
		if($taller2 || !$taller  ) {
		    $this->adjWidth = $this->propWidth;
		    $this->adjHeight = $this->degHeight;
		} else {
		    $this->adjWidth = $this->degWidth;
		    $this->adjHeight = $this->propHeight;   
		}
		
		$this->halfWidth = $this->adjWidth / 2;
		$this->halfHeight = $this->adjHeight / 2;
		
		$this->adjMinLon = $this->centerLon - $this->halfWidth;
		$this->adjMinLat = $this->centerLat - $this->halfHeight;
		$this->adjMaxLon = $this->centerLon + $this->halfWidth;
		$this->adjMaxLat = $this->centerLat + $this->halfHeight;
		if($this->adjMinLon == $this->adjMaxLon ) return;
		if($this->adjMinLat == $this->adjMaxLat) return;
		
	
		$this->minLon = $this->adjMinLon;
		$this->minLat = $this->adjMinLat;
		
		$this->maxLat = $this->adjMaxLat;
		$this->maxLon = $this->adjMaxLon;
		$this->degWidth = $this->maxLon - $this->minLon;
		$this->degHeight = $this->maxLat - $this->minLat;
	}
	public function ToBbox() {
		return "{$this->minLon},{$this->minLat},{$this->maxLon},{$this->maxLat}";
	}
	public function Points2DToDeg() {
		$pointData = func_get_args ();
		
		$ctr = 0;
		$pt = array ();
		$geoPts = array ();
		
		foreach ( $pointData as $datum ) {
			$pt [] = $datum;
			if ($ctr < 2) {
				$ctr ++;
			}
			if ($ctr == 2) {
				
				list ( $x, $y ) = $pt;
				$pt = array ();
				$lon = $this->minLon + (($x / $this->viewWidth) * $this->degWidth);
				$lat = ($this->maxLat - (($y / $this->viewHeight) * $this->degHeight));
				
				$geoPts [] = array (
						'lon' => $lon,
						'lat' => $lat 
				);
				$ctr = 0;
			}
		}
		
		return $geoPts;
	}
	public function GetROI($x1, $y1, $x2, $y2) {
		$geoPts = $this->Points2DToDeg ( $x1, $y1, $x2, $y2 );
		list ( $pt1, $pt2 ) = $geoPts;
		$minLon = min ( $pt1 ['lon'], $pt2 ['lon'] );
		$maxLon = max ( $pt1 ['lon'], $pt2 ['lon'] );
		$minLat = min ( $pt1 ['lat'], $pt2 ['lat'] );
		$maxLat = max ( $pt1 ['lat'], $pt2 ['lat'] );
		return array (
				$minLon,
				$minLat,
				$maxLon,
				$maxLat 
		);
	}
	public function ROI_to_BBOX($x1, $y1, $x2, $y2) {
		$ROI = $this->GetROI ( $x1, $y1, $x2, $y2 );
		return implode ( ',', $ROI );
	
	}
	public function GetViewSize($getAs = 'array') {
		switch ($getAs) {
			case 'array' :
				return array (
						$this->viewWidth,
						$this->viewHeight 
				);
				break;
			case 'node' :
				return "<viewsize width=\"{$this->viewWidth}\" height=\"{$this->viewHeight}\" />";
		}
	}
	public function Resize($width,$height) {
		$degPerPxWidth = $this->degWidth/$this->viewWidth;
		$degPerPxHeight = $this->degHeight/$this->viewHeight;
		$degWidth = $width * $degPerPxWidth;
		$degHeight = $height * $degPerPxHeight;
		$this->maxLon = $this->minLon + $degWidth;
		$this->maxLat  = $this->minLat + $degHeight;
		$this->degWidth = $degWidth;
		$this->degHeight = $degHeight;
		$this->viewWidth = $width;
		$this->viewHeight = $height;
		
	}
	
	public function PaddROI($roi, $padding = 0 ){//.125) {
	   
	    list($minLon,$minLat,$maxLon,$maxLat) = explode(',',$roi);
	    $wkt = "POLYGON(($minLon $minLat, $maxLon $minLat,  $maxLon $maxLat, $minLon $maxLat, $minLon $minLat))";
	    
	    $lonDelta = $maxLon-$maxLon;
	    $latDelta = $maxLat-$minLat;
	    $delta = max($lonDelta,$latDelta);
	    
	    $buffer = $padding;
	    $query = "select ST_XMin(the_geom) as minLon, ST_YMin(the_geom) as minLat, ST_XMax(the_geom) as maxLon, ST_YMax(the_geom) as maxLat from (select ST_Buffer(ST_GeometryFromText('$wkt'),$buffer) as the_geom) as q1";
        
	    $data = \System::GetDB()->GetRow($query,array());
	    $roi = array_values($data);
	    return $roi;
	}
	
	
}

?>