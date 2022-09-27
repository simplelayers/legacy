<?php

class Projector_MapScript
{
	var $pxWidth;
	var $pxHeight;
	var $centerLon;
	var $centerLat;
	var $mapObj;
	
	var $scales = array(314982288,157491144,78745572,39372786,19686393,9843196.5,4921598.25,2460799.125,1230399.5625,615199.78125,307599.890625,153799.9453125,76899.97265625,38449.986328125,19224.993164062,9612.4965820312,4806.2482910156,2403.1241455078,1201.5620727539,600.78103637695);
	var $lonDegPerPxAtLevel=array(1,0.5,0.25,0.125,0.0625,0.03125,0.015625,0.007813889,0.003905556,0.001952778,0.000977778,0.000488889,0.000244444,0.000122222,6.11111E-05,3.05556E-05,1.38889E-05,8.33333E-06,2.77778E-06,1.38889E-06);
	var $latDegPerPxAtLevel=array(1,0.5,0.25,0.125,0.0625,0.03125,0.015625,0.007813889,0.003905556,0.001952778,0.000977778,0.000488889,0.000244444,0.000122222,6.11111E-05,3.05556E-05,1.38889E-05,8.33333E-06,2.77778E-06,1.38889E-06);
	var $degPerLevel = array(360,180,90,45,22.5,11.25,5.625,2.8125,1.40625,0.703125,0.3515625,0.17578125,0.087890625,0.0439453125,0.02197265625,0.010986328125,0.0054931640625,0.00274658203125,0.001373291015625,0.0006866455078125,0.00034332275390625);
	var $levels;
	var $deg_levels;
	function __construct( $map = null )
	{
		$this->mapObj = ($map == null ) ?  ms_newMapObj(null) : $map ;				
		
	}

	public function ProjectExtents( $fromProj4, $toProj4, $pxWidth, $pxHeight, $extents )
	{
		  
			$newExtents = array();
			$this->mapObj->setProjection( $fromProj4 , MS_TRUE );
			$this->SetViewExtents( $extents );
			$this->SetViewSize( $pxWidth, $pxHeight );
			
			$fromExtents = $this->mapObj->extent;
			
			$newExtents["from"] = $this->ExtentsToString( $fromExtents );
			//$this->mapObj->setProjection($toProj4 , MS_TRUE );
			$toExtents = $this->mapObj->extent;
			$newExtents["to"] = $this->ExtentsToString( $toExtents );
			
			
		
			return $newExtents;
	}

	public function GetScale() {
	    $level = $this->GetLevel();
        return $this->deg_levels[$level];
	}

	/**
	 * FitToView
	 * Purpose: Use pixel values for stored square map units and adjust the
	 *          view size and extents so that the desired section of the 
	 *          square map fits the target non-square view size.
	 *          
	 */
	 
	 public function FitToView( $oldSize, $newSize )
	 {
	 	$maxDim = (max($newSize[0],$newSize[1] ) == $newSize[0] ) ? "width" : "height";
		$zoomWidth=0;
		$zoomHeight=0;
		$maxSize =0;
		switch($maxDim) {
			case 'height':
				$maxSize = $zoomWidth = $oldSize[0];
				$ratio = ($newSize[1]/$newSize[0]);
				$zoomHeight = $oldSize[0] *$ratio;
				$centerX = $newSize[0]/2;	
				$centerY = 0+($newSize[1]/2);
				//$centerY = -($oldSize[1]/2) + $zoomHeight;
				break;
			case 'width':

				$maxSize = $zoomHeight = $oldSize[1];
				$ratio = ($newSize[0]/$newSize[1]);
				$zoomWidth = $oldSize[1] *$ratio;
				$centerX = 0+($newSize[0]/2);
				$centerY = $newSize[1]/2;	
				//$centerY = -($oldSize[1]/2) + $zoomHeight;
				break;			
		}
		
		//setup the view rlative to the bbox
		$this->SetViewSize($maxSize,$maxSize);
		$this->ZoomTo(0,0,$oldSize[0],$oldSize[1]);
		
				
		
		$this->SetViewSize($newSize[0],$newSize[1]);
		//$this->ZoomTo(0,0,$newSize[0],$newSize[1]);
		
		$this->mapObj->prepareQuery();
		$exts = $this->mapObj->extent;
		$scaleDenom = $this->mapObj->scaledenom;		
	
		$this->mapObj->prepareQuery();
		
		
		$pos = ms_newPointObj();
		$pos->setXY( $centerX, $centerY);
		//$this->mapObj->zoomScale($scaleDenom,$pos,$oldSize[0],$oldSize[1],$exts);
		//$this->ZoomTo(0,0, $oldSize[0],$oldSize[1]);
		
		$this->CenterAt($centerX ,$centerY);
		
		//$this->CenterAt($zoomWidth,$zoomHeight);
		//$this->CenterAt($centerX,$centerY);
	
//		$this->mapObj->prepareQuery();
		//$this->CenterAt($centerX,$centerY);		

	 
	 }
	 
	 public function GetLevel() {
	     
	     //$scaleDenom = $this->GetScale();
	     $exts = $this->GetROIExtents();
	     $lonDelta = $exts[2]-$exts[0];
	     $latDelta = $exts[3]-$exts[1];
	     $delta = $latDelta;
	     $targetLevel = 19;
	     $deltas = array();
	     foreach($this->deg_levels as $i=>$level) {
	         
             if($level <= $delta) {
                 $deltas[] = $delta;
                 $targetLevel = $i;
	             break;
	         }
	     }
	     
	     if($targetLevel > 19) $targetLevel=19;
	     if($targetLevel < 0) $targetLevel=0;
	     return $targetLevel;
	     
	 }
	 
	 public function NextScale() {
	     $level = $this->GetLevel();
	     $level++;
	     if($level > 19) $level=19;
	     $this->ZoomToLevel($level);
	     return $level;
	     //$this->SetScale($this->levels[$level]);

	 }
	 
	 public function PrevScale() {
	     $level = $this->GetLevel();
	     $level--;
	     
	     if($level < 0 ) $level=0;
	     $this->ZoomToLevel($level);
	     return $level;
	     //$this->SetScale($this->levels[$level]);
	 }
	 
	 
	 
	 
	public function CropToView(  $storedWidth, $storedHeight, $targetWidth, $targetHeight, $offsetX=0, $offsetY=0 ) 
	{
		$storedWidth = (int)$storedWidth;
		$storedHeight = (int)$storedHeight;
		$targetWidth = (int)$targetWidth;
		$targetHeight = (int)$targetHeight;
		$offsetX = (int) $offsetX;
		$offsetY = (int) $offsetY;
		$this->SetViewSize($storedWidth, $storedHeight);
		$this->ZoomTo(0,0,$storedWidth,$storedHeight);
		$exts = $this->mapObj->extent;

		$this->mapObj->prepareQuery();
		
		$scaleDenom = $this->mapObj->scaledenom;
		$this->SetViewSize($targetWidth, $targetHeight);
			
		$pos = ms_newPointObj();
		$pos->setXY( $targetWidth/2-$offsetX,$targetHeight/2-$offsetY);

		//$this->mapObj->zoomscale( $scaleDenom, $pos , $targetWidth, $targetHeight, $exts);
		$this->mapObj->zoomscale( $scaleDenom, $pos , $storedWidth, $storedHeight, $exts);

		$this->SetViewSize($targetWidth, $targetHeight);
		$this->mapObj->prepareQuery();

		

	}
	



	public function SetProjection( $proj4 ) {
	     
		$this->mapObj->setProjection($proj4, true);
	}

	/**
	 * SetViewSize
	 *
	 * Sets the size on the map object, and also stores the view size for future calls.
	 *
	 */
	public function SetViewSize( $pxWidth , $pxHeight )
	{
		
		$this->pxWidth = $pxWidth;
		$this->pxHeight = $pxHeight;
		try{
			//error_reporting(E_ERROR | E_PARSE);
			$this->mapObj->setSize((int) $pxWidth ,(int) $pxHeight );
			
				
		} catch( Exception $e) {
			return;
			//we'll get a warning we'll ignore.
		}

		$this->centerLat = $this->mapObj->extent->miny+($this->mapObj->extent->maxy-$this->mapObj->extent->miny)/2.0;
		$this->centerLon = $this->mapObj->extent->minx+($this->mapObj->extent->maxx-$this->mapObj->extent->minx)/2.0;
		
	
	}

	public function SetViewExtents( $extents )
	{
	    
	    if(!$extents) return false;
		$extents = is_array($extents) ? $extents : explode(",",$extents);
		
		list($minx, $miny , $maxx ,$maxy) = $extents;
				/*hack, setextent is being stupid and truncating/rounding to six digit precision in the current mapscript version
		// we very much need to upgrade.
			/*$diff=0.000000;
			if((float)round($xmin,6)==round($xmax,6)) $diff=0.000001;
			if(round($ymin)==round($ymax)) $diff=0.000001;
			$xmin -= $diff;
			$xmax += $diff;
			$ymin -= $diff;
			$ymax += $diff;
		*/
		
		try {
		    
		    
		    ob_start();
		    $reporting = error_reporting();
		    error_reporting(0);
		      $this->mapObj->setExtent((double) $minx,(double)$miny,(double)$maxx,(double)   $maxy );
		      
		      ob_end_clean();
		      //error_reporting($reporting);
		    /*$xmin = min((double)$minx,(double)$maxx);
		    $ymin = min((double)$miny,(double)$maxy);
		    $xmax = max((double)$minx,(double)$maxx);
		    $ymax = max((double)$miny,(double)$maxy);
		    */
		    //$this->mapObj->setExtent((double) $xmin,(double)$ymin,(double)$xmax, (double)$ymax );
		} catch (Exception $e ) {
		    return;
			
		}
		
		$this->centerLat = $this->mapObj->extent->miny+($this->mapObj->extent->maxy-$this->mapObj->extent->miny)/2.0;
		$this->centerLon = $this->mapObj->extent->minx+($this->mapObj->extent->maxx-$this->mapObj->extent->minx)/2.0;
		
		
	}

	public function ExtentsToString( $ms_extents )
	{
		$minx = min( $ms_extents->minx , $ms_extents->maxx );
		$maxx = max( $ms_extents->minx , $ms_extents->maxx );
		$miny = min( $ms_extents->miny , $ms_extents->maxy );
		$maxy = max( $ms_extents->miny , $ms_extents->maxy );
		return "$minx,$miny,$maxx,$maxy";
	}

	public function GetROIExtents( $getAs = "array", $arg1="projected" )
	{
		$ms_extents = $this->mapObj->extent;
		
		$minx = min( $ms_extents->minx , $ms_extents->maxx );
		$maxx = max( $ms_extents->minx , $ms_extents->maxx );
		$miny = min( $ms_extents->miny , $ms_extents->maxy );
		$maxy = max( $ms_extents->miny , $ms_extents->maxy );

		if( $getAs == "obj" )
		{
				$this->mapObj->extent;
		} 
		if( $getAs == "array" ) {
			return array( $minx , $miny, $maxx, $maxy );
		} 
		if( $getAs == "string" ) {
			return "$minx,$miny,$maxx,$maxy";
		} 
		if( $getAs == "polygon" ) {
			return "POLYGON(($minx $miny,$minx $maxy,$maxx $maxy,$maxx $miny,$minx $miny))";		 
		}
		return "<$arg1 bbox=\"$minx, $miny,$maxx,$maxy\" />";
		
	}


	public function GetROISize($getAs = "array" )
	{
		if( $getAs == "array" )
		{
			return array( $this->pxWidth , $this->pxHeight );
		} else {
			return "<viewsize width=\"{$this->pxWidth}\" height=\"{$this->pxHeight}\" />"; 
		}
	}

	public function SetROI( $x1, $y1, $x2, $y2,$fixExtents=true )
	{
		$rect = ms_newRectObj();
		$rect->setExtent( min( $x1 , $x2 ), min($y1 , $y2 ), max($x1, $x2), max($y1, $y2) );
		if($fixExtents) $this->mapObj->zoomrectangle( $rect , $this->pxWidth , $this->pxHeight );
	}
	
	

   public function CenterAt( $x , $y , $zoomFactor = 1 )
   {
		$pos = ms_newPointObj();
		$pos->setXY((double) $x ,(double) $y );
		//error_log($zoomFactor);
		try{
			$this->mapObj->zoompoint((float) $zoomFactor , $pos , (int)$this->pxWidth ,(int) $this->pxHeight , $this->mapObj->extent );
		}catch(Exception $e) {
			
		}
		$this->centerLat = $this->mapObj->extent->miny+($this->mapObj->extent->maxy-$this->mapObj->extent->miny)/2.0;
		$this->centerLon = $this->mapObj->extent->minx+($this->mapObj->extent->maxx-$this->mapObj->extent->minx)/2.0;
	}

	public function ZoomBy( $zoomFactor )
	{
		$pos = ms_newPointObj();
		//$this->pxWidth = (int) $this->pxWidth;
		//$this->pxHeight = (int) $this->pxHeight;
		$pos->setXY(  181, 91 );
		$this->mapObj->zoompoint((float)$zoomFactor , $pos ,361, 181 , $this->mapObj->extent );
		return;
		
		
		$this->centerLat = $this->mapObj->extent->miny+($this->mapObj->extent->maxy-$this->mapObj->extent->miny)/2.0;
		$this->centerLon = $this->mapObj->extent->minx+($this->mapObj->extent->maxx-$this->mapObj->extent->minx)/2.0;
		$this->SetViewSize($this->pxWidth,$this->pxHeight);
		
	}

	public function ZoomTo($x1 , $y1 ,$x2 ,$y2 )
	{
		$this->CenterAt($this->pxWidth/2,$this->pxHeight/2,1);
		
		$xdiff = round(max($x1,$x2) - min($x1,$x2));
		$ydiff = round(max($y1,$y2) - min($y1,$y2));
		$xratio = $this->pxWidth/$xdiff;
		$yratio = $this->pxHeight/$ydiff;
		$xcen = min($x1,$x2)+($xdiff/2);
		$ycen = min($y1,$y2)+($ydiff/2);
		$avgRatio = min($xratio,$yratio);
		//error_log(var_export(array($xcen,$ycen,$avgRatio),true));
		$this->CenterAt( $xcen , $ycen , $avgRatio);
		return;
		/*
		$rect = ms_newRectObj();
		$rect->setExtent( min($x1, $x2),min($y1,$y2),max($x1,$x2),max($y1,$y2));
		$this->mapObj->zoomrectangle( $rect , $this->pxWidth , $this->pxHeight, $this->mapObj->extent );
		*/
	}

	public function WKTPolyToExtents($wkt) {
		$wkt = str_replace("POLYGON((","",$wkt);
		$wkt = str_replace("))","",$wkt);
		$pts = explode(",",$wkt);
		array_pop($pts);
		$data = array("minx"=>99999,"maxx"=>-99999,"maxy"=>-99999,"miny"=>9999);
		foreach($pts as $pt ) {
			list($x,$y) = explode(" ",$pt);
			$data['minx'] = min((float)$data['minx'],(float)$x);
			$data['miny'] = min((float)$data['miny'],(float)$y);
			$data['maxx'] = max((float)$data['maxx'],(float)$x);
			$data['maxy'] = max((float)$data['maxy'],(float)$y);
		}
		//return $data;
		$this->SetViewExtents(implode(",",$data));//$data['minx'],$data['miny'],$data['maxx'],$data['maxy']);
	}

	public function SetMapCenter($ptOrlon,$lat=null) {
		$ms_extents = $this->mapObj->extent;
		$minx = min( $ms_extents->minx , $ms_extents->maxx );
		$maxx = max( $ms_extents->minx , $ms_extents->maxx );
		$miny = min( $ms_extents->miny , $ms_extents->maxy );
		$maxy = max( $ms_extents->miny , $ms_extents->maxy );
		
		$lonRadDist = ($maxx-$minx)/2.0;
		$latRadDist = ($maxy-$miny)/2.0;
		
		if(is_null($lat)) {
				$pt = $ptOrlon;
			   	$pt = str_replace("POINT(",'',$pt);
				$pt = str_replace(")",'',$pt);
				list($lon,$lat) = explode(" ",$pt);	
		
		} else {
			$lon = $ptOrlon;
		}
		$minx = $lon-$lonRadDist;
		$maxx = $lon+$lonRadDist;
		$miny = $lat-$latRadDist;
		$maxy = $lat+$latRadDist;
		
		$this->mapObj->setExtent( $minx,$miny,$maxx,$maxy);
		$this->centerLat = $this->mapObj->extent->miny+($this->mapObj->extent->maxy-$this->mapObj->extent->miny)/2.0;
		$this->centerLon = $this->mapObj->extent->minx+($this->mapObj->extent->maxx-$this->mapObj->extent->minx)/2.0;
		
	}
	
	public function SetZoomLevel( $level ) {
		$level = (int)$level;
		$ms_extents = $this->mapObj->extent;
		$minx = min( $ms_extents->minx , $ms_extents->maxx );
		$maxx = max( $ms_extents->minx , $ms_extents->maxx );
		$miny = min( $ms_extents->miny , $ms_extents->maxy );
		$maxy = max( $ms_extents->miny , $ms_extents->maxy );
		
		$lonCen = ($maxx-$minx)/2;
		$latCen = ($maxy-$miny)/2;
		
		$degPerPx = $this->levels[$level];
		$degWidth = $this->pxWidth * $degPerPx;
		$degHeight = $this->pxHeight * $degPerPx;
		
		$latRad = $degWidth/2;
		$lonRad = $degHeight/2;
		
		$minx = $lonCen-$lonRad;
		$maxx = $lonCen+$lonRad;
		$miny = $latCen-$latRad;
		$maxy = $latCen+$latRad;
		
		$this->mapObj->setExtent( $minx,$miny,$maxx,$maxy);
		
	}
	
	public function ZoomToLevel($zoomLevel) {
	    
	    $size = array($this->pxWidth,$this->pxHeight);
	    $center = array($this->centerLon,$this->centerLat);
	    $dims = array($this->pxWidth,$this->pxHeight);
	    $x = 181;//($this->pxWidth/2.0);
	    $y = 91;//($this->pxHeight/2.0);
	    $pt = ms_newPointObj();
	    $pt->setXY(181,91);
	    
	    //$this->mapObj->setScale($this->scales[$zoomLevel]);
	    $this->SetViewSize(361,181);
	    $extent = $this->mapObj->extent;
	    $lon = 180/pow(2,$zoomLevel);
	    $lat = 90/pow(2,$zoomLevel);
	    $this->SetViewExtents("-$lon,-$lat,$lon,$lat");
	    $this->SetViewSize($size[0],$size[1]);
	    $this->mapObj->offsetExtent(-180+$lon,-90+$lat);
	    
	    $this->mapObj->offsetExtent(-180+$center[0],-90+$center[1]);
	    
	    
	    
	    return;
	    $this->SetViewExtents('-180,-90,180,90');
	    $this->SetViewSize(361,181);
	    $this->SetMapCenter($center[0],$center[1]);

	    $this->ZoomBy(pow(2,$zoomLevel));
	    $this->SetViewSize($dims[0],$dims[1]);
	    $this->SetViewExtents($this->GetROIExtents('string'));
	     $this->SetViewSize($dims[0],$dims[1]);
	    
	    
	    
	}
	
    public function SetScale($scale) {
        $bbox = $this->GetROI('string');
       
        $this->mapObj->scaleExtent(1,$scale,$scale);
        
        return;
        $pt = ms_newPointObj();
        $pt->setXY($this->centerLon,$this->centerLat);
        $pt = ms_newPointObj();
        $pt->setXY($this->pxWidth/2.0,$this->pxHeight/2.0);
        $this->mapObj->zoomscale((double)$scale, $pt,$this->pxWidth,$this->pxHeight,$this->mapObj->extent);
        //$pt = ms_newPointObj();
        //$pt->setXY($this->centerLon,$this->centerLat);
        $this->CenterAt($pt);
        
        
    }
	
	
	public static function ExtentsToStats($extents) {		
		if(is_string($extents)) {
			if(stripos($extents,',')) $extents = explode(',',$extents);
			if(stripos(trim($extents),' ')) $extents = explode(' ',$extents);			
		}
		list($x1,$y1,$x2,$y2) = $extents;

		$minx = min((float)$x1,(float)$x2);
		$miny = min((float)$y1,(float)$y2);
		$maxx = max((float)$x1,(float)$x2);
		$maxy = max((float)$y1,(float)$y2);

		
		$xD = (($maxx-$minx)/2.0);//-180;
		$yD = (($maxy-$miny)/2.0);//-90;
		$xCen = $minx+$xD;
		$yCen = $maxy-$yD;
		$widthRatio = ($xD*2.0)/($yD*2.0);
		$heightRatio = ($yD*2.0)/($xD*2.0);
		return array('x'=>$xCen,'y'=>$yCen,'halfX'=>$xD,'halfY'=>$yD, 'x_to_y'=>$widthRatio,'y_to_x'=>$heightRatio,'width'=>$xD*2,'height'=>$yD*2);		
	}
	
	
	public function GetCenteredROI($width,$height) {
		$dx = $width/2;
		$dy = $height/2;
		$xcen = $this->width/2;
		$ycen = $this->height/2;
		$x1 = $xcen-$dx;
		$y1 = $ycen-$dy;
		$x2 = $x2+$this->width;
		$y2 = $y2+$this->height;
		return array($x1,$y1,$x2,$y2);
	}

    //public function GetMapLocation( $x , $y );
}

function cg_newProjector($map=null)
{
	return new Projector_MapScript($map);
}

?>
