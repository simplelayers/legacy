<?php
namespace model\mapping;

use utils\ParamUtil;
use utils\ColorUtil;
class Labels {
    
    const POS_AUTO = \MS_AUTO;
    const POS_UL = \MS_UL;
    const POS_CL = \MS_CL;
    const POS_LL = \MS_LL;
    const POS_UC = \MS_UC;
    const POS_CC = \MS_CC;
    const POS_LC = \MS_LC;
    const POS_UR = \MS_UR;
    const POS_CR = \MS_CR;
    const POS_LR = \MS_LR;
    
    private static $positionEnum = null;
    private static $alignEnum = null;
    private static $glowEnum = null;
    
    
    private $defaultPosition = null;
    private $labelStyle = null;
    private $defaults = null;
    public static function GetEnum($which='position') {
        switch($which) {
            case 'position':
                if(!is_null(self::$positionEnum)) return self::$positionEnum;
                self::$positionEnum = new \Enum(array('auto'=>self::POS_AUTO,
                                                            'ul'=>self::POS_UL,'cl'=>self::POS_CL,'ll'=>self::POS_LL,
                                                            'uc'=>self::POS_UC,'cc'=>self::POS_CC,'lc'=>self::POS_LC,
                                                            'ur'=>self::POS_UR,'cr'=>self::POS_CR,'lr'=>self::POS_LR));
                return self::$positionEnum;
            case 'align':
                if(!is_null(self::$alignEnum)) return self::$alignEnum;
                self::$alignEnum = new \Enum(array('left'=>\MS_ALIGN_LEFT,'center'=>\MS_ALIGN_CENTER,'right'=>\MS_ALIGN_RIGHT));
                return self::$alignEnum;
            case 'glow':
                if(!is_null(self::$glowEnum)) return self::$glowEnum;
                self::$alignEnum = new \Enum(array('none'=>0,'small'=>1,'medium'=>3,'large'=>4));
                return self::$glowEnum;
                
        }
        
    }
    
     
    
    public function GetInfo() {
        $info = $this->labelStyle;
        if(is_null($info)) $info = self::GetDefaultStyle();
        foreach($info as $styleName=>$styleVal) {
            if(is_array($styleVal)) {
                
                if( count($styleVal) == 3) {
                    list($r,$g,$b) = $styleVal;
                    $styleVal = ColorUtil::RGB2Web($r,$g,$b);
                }
                $info[$styleName]= $styleVal;
              
            }
            if(is_null($styleVal)) unset($info[$styleName]);
        }
        
        return $info;
    }
    
    
    
    public static function GetLabelsFromALayer($alayer,$defaultPosition=null) {
        if(is_a($alayer,'ProjectLayer')) {
            return self::GetLabelsFromPLayer($alayer,$defaultPosition);
        }
        if(is_a($alayer,'Layer')) {
            return self::GetLabelsFromLayer($alayer,$defaultPosition);
        }
        return null;
    }
    
    public static function GetLabelsFromPLayer(\ProjectLayer $player,$defaultPosition=null) {
        $layer = $player->layer;
        if(is_null($player->label_style)) return self::GetLabelsFromLayer($layer,$defaultPosition);
        if(is_null($defaultPosition)) {
            $enum = self::GetEnum();
            $defaultPosition = ($layer->geomtype == \GeomTypes::POLYGON) ? self::POS_CC : self::POS_AUTO;
            $defaultPosition = $enum[$defaultPosition];
        }
        return new Labels($player->label_style,$defaultPosition);
        
    }
    
    public static function GetLabelsFromLayer(\Layer $layer,$defaultPosition=null) {
        
        if(is_null($defaultPosition)) {
            $enum = self::GetEnum();
            $defaultPosition = ($layer->geomtype == \GeomTypes::POLYGON) ? self::POS_CC : self::POS_AUTO;
            $defaultPosition = $enum[$defaultPosition]; 
        } 
        return new Labels($layer->label_style,$defaultPosition);
        
    }
    
    public static function GetLabelsFromLayerId($id,$defaultPosition=null) {
        $layer = \Layer::GetLayer($id,true);
        return self::GetLabelsFromLayer($layer,$defaultPosition);
    }
    
    public static function GetLabelsFromPLayerId($id,$defaultPosition=null) {
        $layer = \ProjectLayer::Get($id);
        return self::GetLabelsFromPLayer($layer,$defaultPosition);
    }
    
    
    public static function SaveLabelStyle($aLayer,$info) {
        if(!(is_a($aLayer,'ProjectLayer') || is_a($aLayer,'Layer') )) throw new \Exception('Attempting to save labels for invalid layer');
        $alignEnum = self::GetEnum('align');
        $positionEnum = self::GetEnuM('position');
        $labelInfo['position'] = $positionEnum[$info['position']];
        $labelInfo['align'] = $positionEnum[$info['align']];
        $aLayer->label_style = $info;
    }
    
    public function __construct($labelStyle=null,$defaultPosition) {
        $this->defaultPosition = $defaultPosition;
        if(is_null($labelStyle)) $labelStyle = $this->GetDefaultStyle();
        $this->labelStyle = is_array($labelStyle) ? $labelStyle : json_decode($labelStyle,true);
        
    }
    
    public function __get($infoProp) {
        
        $info =  ParamUtil::Get($this->labelStyle,$infoProp);
        if(is_null($info)) {
            $defaults = $this->GetDefaultStyle();
            $info = ParamUtil::Get($defaults,$infoProp);
        }
        return $info;
    }           
    
    
    public function GetDefaultStyle() {
        $alignEnum = self::GetEnum('align');
        $positionEnum = self::GetEnum('position');
        if(!is_null($this->defaults)) return $this->defaults;
        $labelInfo = array();
        $labelInfo['color'] = "#000000";
        $labelInfo['outlinecolor'] = "#cccccc";
        $labelInfo['outlinewidth'] = 1;
        //$this->defaultPosition  = self::POS_UL;
        $labelInfo['position'] = $positionEnum[$this->defaultPosition];
        $labelInfo['size'] = 7;
        $labelInfo['font'] = 'Vera';
        $labelInfo['mindistance'] = 0;
        $labelInfo['buffer'] = 1;
        $labelInfo['partials'] = \MS_TRUE;
        $labelInfo['antialias'] = \MS_FALSE;
        $labelInfo['minfeaturesize'] = -128;
        $labelInfo['offsetx'] = 0;
        $labelInfo['offsety'] = 0;
        $labelInfo['shadowsizex']=0;
        $labelInfo['shadowsizey']=0;
        $labelInfo['shadowcolor']= null;
        $labelInfo['antialias'] = \MS_FALSE;
        
        $labelInfo['align'] =  $alignEnum[\MS_ALIGN_CENTER];
        $labelInfo['angle'] = 0;
        $this->defaults = $labelInfo;
        
        return $this->defaults;
        
    }
    
    public function UpdateLabel($label) {
        $alignEnum = self::GetEnum('align');
        $labelInfo = $this->labelStyle;
        
        $enum = self::GetEnum();
        $defaults = $this->GetDefaultStyle();
        $r=$g=$b=0;
        $color = ParamUtil::Get($labelInfo,'color',$defaults['color']);
        ColorUtil::Web2RGB($color, $r, $g, $b);
        //list($r,$g,$b) = ParamUtil::Get($labelInfo,'color',$defaults['color']);
        $label->color->setRGB($r,$g,$b);
        
        
        $outlineColor = ParamUtil::Get($labelInfo,'outlinecolor',$defaults['outlinecolor']);
        if(!is_null($outlineColor)) {
            ColorUtil::Web2RGB($outlineColor, $r, $g, $b);
            $label->outlinecolor->setRGB($r, $g, $b);
        }
        
        $label->offsetx = ParamUtil::Get($labelInfo,'offsetx',$defaults['offsetx']);
        $label->offsety = ParamUtil::Get($labelInfo,'offsety',$defaults['offsety']);
        
        $shadowColor = ParamUtil::Get($labelInfo,'shadowcolor',$defaults['shadowcolor']);
        if(isset($labelInfo['shadowcolor'])) {
            ColorUtil::Web2RGB($shadowColor, $r, $g, $b);
            $label->shadowcolor->setRGB($r, $g, $b);
            $label->shadowsizex= ParamUtil::Get($labelInfo,'shadowsizex',$defaults['shadowsizex']);
            $label->shadowsizey=ParamUtil::Get($labelInfo,'shadowsizex',$defaults['shadowsizex']);
            
        }
       
        $label->outlinewidth= ParamUtil::Get($labelInfo,'outlinewidth',$defaults['outlinewidth']);
        $position = ParamUtil::Get($labelInfo,'position',$defaults['position']);
        $position = $enum[$position];
        $label->position=$position;
        $label->buffer=ParamUtil::Get($labelInfo,'buffer',$defaults['buffer']);
        
        
        $label->set('size', ParamUtil::Get($labelInfo,'size',$defaults['size']));
        //$label->set('width', ParamUtil::Get($labelInfo,'size',$defaults['size']));
        if(defined('IS_DEV_SANDBOX')) {
            $label->set('type',\ MS_TRUETYPE);
        }
        $label->set('font', ParamUtil::Get($labelInfo,'font',$defaults['font']));
        $label->set('align', $alignEnum[ParamUtil::Get($labelInfo,'align',$defaults['align'])]);
        $label->set('angle', ParamUtil::Get($labelInfo,'angle',$defaults['angle']));
        
        $label->set('mindistance', ParamUtil::Get($labelInfo,'mindistance',$defaults['mindistance']));
        $label->set('buffer', ParamUtil::Get($labelInfo,'buffer',$defaults['buffer']));
        $label->set('partials', ParamUtil::Get($labelInfo,'partials',$defaults['partials']));
        $label->set('minfeaturesize', ParamUtil::Get($labelInfo,'minfeaturesize',$defaults['minfeaturesize']));
        //$label->updateFromString('ANTIALIAS = $antialias;
        return;
    }
    
}




?>
