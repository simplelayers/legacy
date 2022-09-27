<?php
namespace model\mapping;

class PixoSpatial
{

    private $view;

    private $ROI;

    private $degPerPx;

    public function __construct($bbox, $viewWidth, $viewHeight)
    {
        
        $this->view = new PixoView($viewWidth, $viewHeight);
        $this->ROI = new PixoROI($bbox);
        $this->FitToLevel($this->DetectLevel());
    }

    public function FitToLevel($level)
    {
        $this->ROI->SetLevel($level);
        $this->ROI->FitToView($this->view);
    }

    public function MoveToROI($bbox,$pxBuffer=0)
    {
        if(is_array($bbox)) {
            $bbox = implode(',',$bbox);//array($minLon,$minLat,$maxLon,$maxLat));
        }
        //$=$pctBuffer * max($this->view->width,$this->view->height);
        $this->ROI->MoveToROI($this->view,$bbox,$pxBuffer);
        
        /*$this->ROI->SetBBox($bboxOrMinLon, $minLat, $maxLon, $maxLat);
        //$this->ROI->FitToView($this->view,$bboxOrMinLon,$bbox);
        
        $this->ROI->FitToView($this->view);
        $this->ROI->SetBBox($bboxOrMinLon, $minLat, $maxLon, $maxLat);
        $this->FitToLevel($this->DetectLevel());
        */
    }

    public function Resize($width, $height)
    {
        $deltas = $this->view->Resize($width, $height);
        
        $this->ROI->ResizeView($deltas['xDelta'], $deltas['yDelta']);
        
        // $this->FitToLevel($this->GetLevel());
    }

    public function ZoomNext()
    {
        $level = $this->GetLevel();
         $newLevel = $level+1;
        $this->FitToLevel($newLevel);
    }

    public function ZoomPrev()
    {
        $level = $this->GetLevel();
        $newLevel = $level-1;
        $this->FitToLevel($newLevel);
    }

    public function DetectLevel()
    {
        $scaledSize = $this->ROI->GetViewDegSize($this->view);
        $degPerPx = max($scaledSize['degWidth'], $scaledSize['degHeight']);
        return $this->ROI->GetNearestLevel($degPerPx);
    }

    
    public function SetInitialLevel() {
        $this->FitToLevel( $this->ROI->CalculateInitialLevel($this->view));
    }
    
    public function CenterROI($lon, $lat)
    {
        $this->ROI->SetCenter($this->view, $lon, $lat);
    }

    public function MoveROI($lonDelta, $latDelta)
    {
        $this->ROI->MoveBy($lonDelta, $latDelta);
    }

    public function GetViewROI($x1, $y1, $x2, $y2)
    {
        return $this->ROI->GetViewROI($this->view,$x1, $y1, $x2, $y2);
    }

    public function GetViewPoint($x,$y) {
        return $this->ROI->GetViewPoint($this->view,$x,$y);
        
        
    }
    
    public function MoveToViewRect($x1, $y1, $x2, $y2)
    {
         $pxWidth = ($x2 - $x1);
        $pxHeight = ($y2 - $y1);
        //
        $centerX =  $x1+($pxWidth / 2.0);
        $centerY = $y1+($pxHeight / 2.0);
        
        
        
        $pxWidth2=$pxWidth;
        $pxHeight2=$pxHeight;
        
        $widthPerHeight = $this->view->width/$this->view->height;
        $heightPerWidth = 1.0/$widthPerHeight;

       
        if($widthPerHeight <=1 ) {
            $pxHeight2=$heightPerWidth*$pxWidth2;
            
        } else {
            $pxWidth2=$widthPerHeight*$pxHeight2;
        }
        
        $minX = $centerX - ($pxWidth2/2.0);
        $maxX = $centerX + ($pxWidth2/2.0);
        $minY = $centerY - ($pxHeight2/2.0);
        $maxY = $centerY + ($pxHeight2/2.0);

        $exts = $this->ROI->GetViewRect($this->view, $minX, $minY, $maxX, $maxY);
        //$this->MoveToViewPoint($centerX, $centerY);
        //var_dump($this->ROI->extentString);
        
        /*$degPerPxWidth = ($this->ROI->maxLon-$this->ROI->minLon) / $pxWidth;
        $degPerPxHeight = ($this->ROI->maxLat-$this->ROI->minLat) / $pxHeight;
        
        $degPerPxHeight2 = $degPerPxHeight * $pxHeight2;
        $degPerPxWidth2 = $degPerPxWidth * $pxWidth2;
        
        
        $degWidth = ($degPerPxWidth2 / 2.0);// * $this->ROI->degPerPx;
        $degHeight = ($degPerPxHeight2 / 2.0);// * $this->ROI->degPerPx;
        
        
        //$minLon = $this->ROI->center['lon'] - $degWidth;
        //$minLat = $this->ROI->center['lat'] - $degHeight;
        //$maxLon = $this->ROI->center['lon'] + $degWidth;
        //$maxLat = $this->ROI->center['lat'] + $degHeight;
        
        */
        //$this->ROI->SetBbox($exts);//$minLon, $minLat, $maxLon, $maxLat);
        $this->MovetoROI(array_values($exts),0);
        
        //
        //$level = $this->DetectLevel();
        //$this->FitToLevel($level);
    }

    public function MoveToViewPoint($x, $y)
    {
        $this->ROI->MoveToViewPoint($this->view, $x, $y);
    }

    public function GetBBox($fmt = 'string')
    {
        switch ($fmt) {
            case 'array':
                return explode(',', $this->ROI->extentString);
            case 'string':
            default:
                return $this->ROI->extentString;
        }
    }

    public function GetWidth()
    {
        return $this->view->width;
    }

    public function GetHeight()
    {
        return $this->view->height;
    }

    public function GetLevel()
    {
        return $this->ROI->level;
    }

    public function GetViewInfo()
    {
        $viewInfo = array(
            'width' => (float) $this->view->width,
            'height' => (float) $this->view->height
        );
        return $viewInfo;
    }
    
    public function GetROICenter() {
        return  $this->ROI->center;
    }
}

?>