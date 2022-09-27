<?php
namespace model\mapping;

class PixoView {
    public $width;
    public $height;
    
    public $midWidth;
    public $midHeight;
    
    public $scaleX;
    public $scaleY;
    
    public function __construct($width,$height) {
        $this->SetSize($width,$height);
        
    }
    
    public function SetSize($width,$height) {
        $this->width = $width;
        $this->height= $height;
        $this->midWidth = $this->width/2.0;
        $this->midHeight = $this->height/2.0;
        $this->scaleX = $this->width/360;
        $this->scaleY = $this->height/180;
    }
    
    
    public function GetSize() {
        return array('width'=>$this->width,'height'=>$this->height);
        
    }
    
    public function ToString() {
        return implode(',',$this->GetSize());
        
    }
    
    public function Resize($newWidth,$newHeight) {
        $xDelta = $newWidth-$this->width;
        $yDelta = $newHeight - $this->height;
        $this->SetSize($newWidth,$newHeight);
        return array('xDelta'=>$xDelta,'yDelta'=>$yDelta);
       
    }
    
    
}
?>