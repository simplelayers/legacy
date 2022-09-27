<?php
namespace model\mapping;

class ALayer
{
    
    public static function GetLayers($aLayer) {
        
        $layers =  array('layer'=>null,'player'=>null);
        if(is_a($aLayer,'\ProjectLayer' )) {
            $layers['player'] = $aLayer;
            $layers['layer'] = $aLayer->layer;
        } else {
            $layers['player'] = null;
            $layers['layer'] = $aLayer;   
        }
        return array($layers['player'],$layers['layer']);
        
    }
}

?>