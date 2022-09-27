<?php
namespace model\mapping;

/**
 * Region Of Interest for PixoSpatail
 * Region of Interest is in map coordinates
 * In this case it is in lon/lat degrees per
 * projection ESPG:4326
 *
 *
 *
 * @author Arthur
 *        
 */
class PixoROI
{

    public $minLon;

    public $minLat;

    public $maxLon;

    public $maxLat;

    public $center = array(
        'lon' => null,
        'lat' => null
    );

    public $degPerPx;

    public $level;

    public $deltaLon;

    public $deltaLat;

    const projection = 'EPSG:4326';
    const max_levels = 21;
    const min_level = 2;
    const px_per_tile = 256;
    
   var $degPerPxAtLevel = array( 1.40625,0.703125,0.3515625,0.17578125,0.087890625,0.043945313,0.021972656,0.010986328,0.005493164,0.002746582,0.001373291,0.000686646,0.000343323,0.000171661,8.58307E-05,4.29153E-05,2.14577E-05,1.07288E-05,5.36442E-06,2.68221E-06,1.3411E-06,6.70552E-07);

    public function __construct($bbox)
    {
        $this->SetBBOX($bbox);
    }

    public function __get($what)
    {
        switch ($what) {
            case 'extentString':
                $bbox = '';
                $bbox .= $this->minLon . ',';
                $bbox .= $this->minLat . ',';
                $bbox .= $this->maxLon . ',';
                $bbox .= $this->maxLat;
                return $bbox;
                break;
        }
    }

    public function SetBbox($bboxOrMinLon, $minLat = null, $maxLon = null, $maxLat = null)
    {
        $minLon = $bboxOrMinLon;
        if (is_null($minLat)) {
            if (is_array($bboxOrMinLon)) {
                list ($minLon, $minLat, $maxLon, $maxLat) = array_values($bboxOrMinLon);
            } else {
                list ($minLon, $minLat, $maxLon, $maxLat) = explode(',', $bboxOrMinLon);
            }
        }
       
        $this->minLon = min($maxLon, $minLon);
        $this->minLat = min($maxLat, $minLat);
        
        $this->maxLon = max($maxLon, $minLon);
        $this->maxLat = max($maxLat, $minLat);
        
        $this->deltaLon = $maxLon - $minLon;
        $this->deltaLat = $maxLat - $minLat;
        
        $this->center['lon'] = $this->minLon + ($this->deltaLon / 2.0);
        $this->center['lat'] = $this->minLat + ($this->deltaLat / 2.0);
    }

    public function SetScale($degPerPx)
    {
        $this->degPerPx = $degPerPx;
    }

    public function SetLevel($level)
    {
        
        if ($level < self::min_level) $level = self::min_level;
        if ($level > self::max_levels)$level = self::max_levels;
        $this->level = $level;
        
        $this->SetScale($this->degPerPxAtLevel[$level]);
    }

    public function GetNearestLevel($targetDegPerPx)
    {
         
        //var_dump($this->degPerPxAtLevel);
        $i=0;
        foreach ($this->degPerPxAtLevel as $level => $degPerPx) {
            //var_dump(''.number_format($targetDegPerPx,16).' '.number_format($degPerPx,16).' '.$level);
            $nearestLevel=$level;
            //var_dump(bccomp($targetDegPerPx, $degPerPx,5));
            if(number_format($targetDegPerPx*10000,5)===number_format($degPerPx*10000,5)) {
               // $nearestLevel = $level;
                break;
            } 
              
            if (bccomp($targetDegPerPx*10000000, $degPerPx*10000000,128) >-1) {
                //$nearestLevel = $level;
                break;
            }
        }
        
       //var_dump("===");
        return $nearestLevel;
    }

    public function GetViewDegSize(PixoView $view)
    {
        //var_dump($view->width,$this->deltaLon);
        
        $maxPx = 360.0*$view->width/$this->deltaLon;
        $degPerPx = (360.0/$maxPx)-floor(360.0/$maxPx);
        
        //var_dump($maxPx);
        //var_dump($degPerPx);
        
        $degPerPxLon = $degPerPx;//$this->deltaLon / $view->width;
        $degPerPxLat = $degPerPx    ;//$this->deltaLat / $view->height;
        
        return array(
            'degWidth' => $degPerPxLon,
            'degHeight' => $degPerPxLat
        );
    }

    public function FitToView(PixoView $view)
    {
        
        $midWidthLon = $view->midWidth * $this->degPerPx;
        $midHeightLat = $view->midHeight * $this->degPerPx;
        
        $bbox = array();
        $bbox[] = $this->center['lon'] - $midWidthLon;
        $bbox[] = $this->center['lat'] - $midHeightLat;
        $bbox[] = $this->center['lon'] + $midWidthLon;
        $bbox[] = $this->center['lat'] + $midHeightLat;
        
        $this->SetBbox($bbox);
        
        
    }

    public function SetCenter(PixoView $view, $lon, $lat)
    {
        $this->center['lon'] = $lon;
        $this->center['lat'] = $lat;
        $this->FitToView($view);
    }

    public function MoveToViewPoint(PixoView $view, $x, $y)
    {
        $xPctWidth = $x/$view->width;
        $yPctHeight = $y/$view->height;
        
        list($lon,$lat) = array_values($this->GetViewPoint($view,$x,$y));
        $this->center['lon'] = $lon;
        $this->center['lat'] = $lat;
        
        $this->minLon = $this->center['lon'] - $this->deltaLon / 2.0;
        $this->minLat = $this->center['lat'] - $this->deltaLat / 2.0;
        $this->maxLon = $this->center['lon'] + $this->deltaLon / 2.0;
        $this->maxLat = $this->center['lat'] + $this->deltaLat / 2.0;
    }
    
    public function GetViewPoint(PixoView $view,$x,$y) {
        $xPctWidth = $x/$view->width;
        $yPctHeight = $y/$view->height;
        
        $lon = $this->minLon+(($this->deltaLon*$xPctWidth));// $this->minLon + ($this->degPerPx * $x);
        $lat = $this->maxLat-(($this->deltaLat*$yPctHeight));//$this->maxLat - ($this->degPerPx * $y);
        return array('lon'=>$lon,'lat'=>$lat);
    }
    
    public function GetViewRect(Pixoview $view,$x1,$y1,$x2,$y2) {
        list($llLon,$llLat) = array_values($this->GetViewPoint($view,$x1,$y2));
        list($urLon,$urLat) = array_values($this->GetViewPoint($view,$x2,$y1));
        $minLon = min($llLon,$urLon);
        $minLat = min($llLat,$urLat);
        $maxLon =  max($llLon,$urLon);
        $maxLat = max($llLat,$urLat);
        return array('minLon'=>$minLon,'minLat'=>$minLat,'maxLon'=>$maxLon,'maxLat'=>$maxLat);
        
    }
    
    public function MoveBy($lonDelta, $latDelta)
    {
        $minLon = $this->minLon + $lonDelta;
        $minLat = $this->minLat + $latDelta;
        $maxLon = $this->maxLon + $lonDelta;
        $maxLat = $this->maxLat + $latDelta;
        $this->SetBbox($minLon, $minLat, $maxLon, $maxLat);
    }
    
    public function AdjustROI(PixoView $view ,$bbox) {
        $roi = new PixoROI($bbox);
        $minLon = $roi->minLon;
        $minLat = $roi->minLat;
        $maxLon = $roi->maxLon;
        $maxLat = $roi->maxLat;
        
        if($view->width >$view->height) {
            if($roi->deltaLat/2.0 > $roi->deltaLon) {
                $degPerPx = ($maxLon-$minLon)/$view->height;
                $minLon = $roi->center->lon-($degPerPx*($view->width/2));
                $maxLon = $roi->center->lon-($degPerPx*($view->width/2));
            }            
        } elseif ($view->height > $view->width) {
            if($roi->deltaLon > $roi->deltaLat/2.0) {
                $degPerPx = ($maxLat-$minLat)/$view->width;
                $minLat = $roi->center->lat-($degPerPx*($view->height/2));
                $maxLat = $roi->center->lat-($degPerPx*($view->height/2));
            }            
        }
        $bbox = implode(',',array($minLon,$minLat,$maxLon,$maxLat));
        return $bbox;
        
    }
    
    
    public function MoveToROI(PixoView $view,$bbox,$pxBuffer=0) {
        
       // $bbox  = $this->AdjustROI($view,$bbox);
        
        // bbox entries for target ROI
        list($minLon,$minLat,$maxLon,$maxLat) = explode(',',$bbox);
       
        $deltaLon = $maxLon-$minLon;
        $delatLat = $maxLat-$minLat;
        
        $this->SetCenter($view,$minLon+(($maxLon-$minLon)/2.0),$maxLat-(($maxLat-$minLat)/2.0));
        $this->SetBbox($bbox);
        $pxBuffer=0;
        
        // Zoom all the way in on center and then zoom back until the original extents fit thenew extents.
        for($i=self::max_levels;$i>=0;$i--) {
            
            $this->SetLevel($i);
            $this->FitToView($view);
            $degBuffer = $pxBuffer * $this->degPerPx;
            //$pxBuffer=0;
            $checks = 0;
            list($minLon2,$minLat2,$maxLon2,$maxLat2) = $this->GetViewROI($view,$pxBuffer,$pxBuffer,$view->width-$pxBuffer,$view->height-$pxBuffer);
            
            if(floatval(''.$minLon2) <=  floatval(''.$minLon))//$minLon-$degBuffer) 
            {
                $checks++;
            }
            if(floatval(''.$minLat2) <= floatval(''.$minLat))// $minLat-2*$degBuffer) 
            {
                $checks++;
            }
            if(floatval(''.$maxLon2) >= floatval(''.$maxLon))//$maxLon+$degBuffer) 
            {
                $checks++;
            }
            if(floatval(''.$maxLat2) >= floatval(''.$maxLat))//$maxLat+2*$degBuffer) 
            {
                $checks++;
            }
            
            if($checks==4) {
                //$this->SetLevel($i-1);
                //$this->FitToView($view);
                
                break;
            }
            
            
        }
       
      
    
        
        
    }

    public function ResizeView($xDelta, $yDelta)
    {
        $lonMax = $this->maxLon + ($xDelta * $this->degPerPx);
        $latMax = $this->maxLat - ($yDelta * $this->degPerPx);
        $bbox = array(
            $this->minLon,
            $this->minLat,
            $lonMax,
            $latMax
        );
        $this->SetBbox($bbox);
    }

    public function GetViewROI(PixoView $view,$x1, $y1, $x2, $y2)
    {
        //var_dump($this->maxLon,$this->maxLat);
        $degPerPxWidth = ($this->deltaLon)/($view->width);
        $degPerPxHeight = ($this->deltaLat)/($view->height);
       
        list($lonMin,$latMax) = array_values( $this->GetViewPoint($view,$x1,$y1) );
        list($lonMax,$latMin) = array_values($this->GetViewPoint($view,$x2,$y2));
        /*$lonMin = $this->minLon + ($x1 * $degPerPxWidth);
        $lonMax = $this->minLon + ($x2 * $degPerPxWidth);
        $latMax = $this->minLat + ($y1 * $degPerPxHeight);
        $latMin = $this->minLat + ($y2 * $degPerPxHeight);*/
        
        $roi = array(
            $lonMin,
            $latMin,
            $lonMax,
            $latMax
        );
        
        return $roi;
    }
    
    public function CalculateInitialLevel($view) {
             $zW = $zH = null;
        
         
        for($z=self::min_level;$z<self::max_levels; $z++) {
            $t = pow(2,$z);
            //var_dump("Z=$z T=$t");
            $degPerTileLon = $t/360;
            $degPerTileLat = $t/180;
            $lonDeltaT=$this->deltaLon*$degPerTileLon;
            $latDeltaT=$this->deltaLat*$degPerTileLat;
        
            // var_dump($lonDeltaT,$latDeltaT);
             
            $width=$lonDeltaT * self::px_per_tile;
            $height=$latDeltaT * self::px_per_tile;
        
            //var_dump('px dims');
            //var_dump($width,$height);
            if($view->width <= $view->height) {
                if($width >=$view->width){
                    $z-=1;
                    break;
                }
            }
            if($view->height <= $view->width) {
                if($height >= $view->height) {
                    $z-=1;
                    break;
                }
            }
        
        }
        return $z;
    }
    
}

?>
