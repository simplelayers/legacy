<?php

use utils\ParamUtil;
use model\media\FeatureImage;

function _exec()
{
    $params = WAPI::GetParams();
    
    $layerId = ParamUtil::RequiresOne($params, 'layerId');
    $featureId = ParamUtil::RequiresOne($params, 'featureId');
    $field = ParamUtil::RequiresOne($params,'field');
    $action = ParamUtil::Get($params,'action','get');
    
    switch($action) {
        case 'get':
            $asDownload = ParamUtil::Get($params,'as_download',false);
            FeatureImage::GetImage($layerId,$featureId,$field,$asDownload);
            break;                
    }
    
    
    
}
?>