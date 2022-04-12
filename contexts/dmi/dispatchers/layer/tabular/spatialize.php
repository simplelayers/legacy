<?php

use utils\ParamUtil;
use utils\SQLUtil;

function _dispatch_spatialize($template, $args, $org, $pageArgs) {
    $wapi = System::GetWapi();
    $layer = null;
    $type = null;
 
    try {
        $layer = $wapi->RequireLayer(LayerTypes::RELATABLE);
        $type = ParamUtil::RequiresOne($wapi->GetParams(), 'geom','type', 'geomtype');
        # \System::GetDB()->debug  = true;
        $layer->Spatialize($type);
        
        redirect('?do=layer.edit1&id='.$layer->id);
    } catch (Exception $e) {
        die();
        error_log($e->getMessage());
        if ($layer) {
            redirect('?do=layer.tabular.edit&id='.$layer->id);
        } else {
            redirect('?do=layerlist');
        }
        javascriptalert('There was a problem spatializing your layer');

    }
    
}
